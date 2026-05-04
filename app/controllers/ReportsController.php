<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../models/ProductModel.php';

class ReportsController extends Controller {
    private SaleModel $saleModel;
    private ProductModel $productModel;

    public function __construct() {
        $this->requireAdmin();
        $this->saleModel = new SaleModel();
        $this->productModel = new ProductModel();
    }

    public function index(): void {
        $date = $_GET['date'] ?? date('Y-m-d');
        $summary = $this->saleModel->getDailySummary($date);
        $sales = $this->saleModel->findAllWithDetails([
            'date_from' => $date,
            'date_to' => $date,
            'status' => 'completed',
        ]);
        $this->view('reports/daily', [
            'pageTitle' => 'Daily Report',
            'summary' => $summary,
            'sales' => $sales,
            'date' => $date,
        ]);
    }

    public function monthly(): void {
        $year = (int)($_GET['year'] ?? date('Y'));
        $monthly = $this->saleModel->getMonthlySales($year);
        $this->view('reports/monthly', [
            'pageTitle' => 'Monthly Report',
            'monthly' => $monthly,
            'year' => $year,
        ]);
    }

    public function products(): void {
        $topProducts = $this->saleModel->getTopProducts(20);
        $lowStock = $this->productModel->getLowStock();
        $this->view('reports/products', [
            'pageTitle' => 'Product Reports',
            'topProducts' => $topProducts,
            'lowStock' => $lowStock,
        ]);
    }
}
