<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class SalesController extends Controller {
    private SaleModel $saleModel;
    private ProductModel $productModel;
    private CustomerModel $customerModel;

    public function __construct() {
        $this->saleModel = new SaleModel();
        $this->productModel = new ProductModel();
        $this->customerModel = new CustomerModel();
    }

    public function index(): void {
        $products = $this->productModel->findActive();
        $customers = $this->customerModel->findAll('', 'name ASC');

        $this->view('sales/pos', [
            'pageTitle' => 'POS Terminal',
            'products' => $products,
            'customers' => $customers,
        ]);
    }

    public function process(): void {
        if (!$this->isPost()) {
            $this->json(['success' => false, 'message' => 'Invalid request'], 400);
        }

        $input = json_decode(file_get_contents('php://input'), true);
        if (!$input) {
            $this->json(['success' => false, 'message' => 'Invalid data'], 400);
        }

        $items = $input['items'] ?? [];
        if (empty($items)) {
            $this->json(['success' => false, 'message' => 'No items in cart'], 400);
        }

        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += (float)$item['subtotal'];
        }

        $taxRate = (float)($input['tax_rate'] ?? TAX_RATE);
        $discount = (float)($input['discount_amount'] ?? 0);
        $taxAmount = ($subtotal - $discount) * ($taxRate / 100);
        $total = $subtotal - $discount + $taxAmount;
        $paid = (float)($input['amount_paid'] ?? 0);
        $change = $paid - $total;

        if ($paid < $total) {
            $this->json(['success' => false, 'message' => 'Insufficient payment amount'], 400);
        }

        $saleData = [
            'customer_id' => $input['customer_id'] ?? null,
            'user_id' => $_SESSION['user_id'],
            'subtotal' => $subtotal,
            'tax_rate' => $taxRate,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discount,
            'total_amount' => $total,
            'amount_paid' => $paid,
            'change_amount' => max(0, $change),
            'payment_method' => $input['payment_method'] ?? 'cash',
            'notes' => $input['notes'] ?? '',
        ];

        $saleId = $this->saleModel->createSale($saleData, $items);

        if ($saleId) {
            $sale = $this->saleModel->findByIdWithItems($saleId);
            $this->json(['success' => true, 'sale' => $sale]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to process sale'], 500);
        }
    }

    public function searchProduct(): void {
        $search = $this->sanitize($_GET['q'] ?? '');
        $products = $this->productModel->findActive($search);
        $this->json(['products' => $products]);
    }

    public function getBySku(): void {
        $sku = $this->sanitize($_GET['sku'] ?? '');
        $product = $this->productModel->findBySku($sku);
        $this->json(['product' => $product]);
    }

    public function history(): void {
        $filters = [
            'date_from' => $_GET['date_from'] ?? date('Y-m-01'),
            'date_to' => $_GET['date_to'] ?? date('Y-m-d'),
            'status' => $_GET['status'] ?? '',
        ];

        $sales = $this->saleModel->findAllWithDetails($filters);
        $this->view('sales/history', [
            'pageTitle' => 'Sales History',
            'sales' => $sales,
            'filters' => $filters,
            'flash' => $this->getFlash(),
        ]);
    }

    public function show($id): void {
        $sale = $this->saleModel->findByIdWithItems((int)$id);
        if (!$sale) {
            $this->setFlash('error', 'Sale not found.');
            $this->redirect('sales/history');
        }

        $this->view('sales/receipt', [
            'pageTitle' => 'Receipt #' . $sale['invoice_number'],
            'sale' => $sale,
        ]);
    }

    public function void($id): void {
        $this->requireAdmin();
        $success = $this->saleModel->voidSale((int)$id);
        if ($success) {
            $this->setFlash('success', 'Sale voided successfully.');
        } else {
            $this->setFlash('error', 'Failed to void sale.');
        }
        $this->redirect('sales/history');
    }

    public function searchCustomer(): void {
        $q = $this->sanitize($_GET['q'] ?? '');
        $customers = $this->customerModel->search($q);
        $this->json(['customers' => $customers]);
    }
}