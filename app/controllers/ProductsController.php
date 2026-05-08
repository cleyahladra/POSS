<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CategoryModel.php';

class ProductsController extends Controller {
    private ProductModel $productModel;
    private CategoryModel $categoryModel;

    public function __construct() {
        $this->productModel = new ProductModel();
        $this->categoryModel = new CategoryModel();
    }

    public function index(): void {
        $search = $_GET['search'] ?? '';
        $products = $this->productModel->findAllWithCategory($search);
        $this->view('products/index', [
            'pageTitle' => 'Products',
            'products' => $products,
            'search' => $search,
            'flash' => $this->getFlash(),
        ]);
    }

    public function create(): void {
        $this->requireAdmin();
        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'sku' => $this->sanitize($_POST['sku'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'price' => $_POST['price'] ?? 0,
                'cost' => $_POST['cost'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'low_stock_alert' => $_POST['low_stock_alert'] ?? 10,
                'category_id' => $_POST['category_id'] ?? null,
                'is_active' => 1,
            ];

            $id = $this->productModel->create($data);
            if ($id) {
                $this->setFlash('success', 'Product created successfully!');
            } else {
                $this->setFlash('error', 'Failed to create product.');
            }
            $this->redirect('products');
        }

        $categories = $this->categoryModel->findAll('', 'name ASC');
        $this->view('products/form', [
            'pageTitle' => 'Add Product',
            'categories' => $categories,
            'product' => null,
        ]);
    }

    public function edit($id): void {
        $this->requireAdmin();
        $product = $this->productModel->findById((int)$id);
        if (!$product) {
            $this->setFlash('error', 'Product not found.');
            $this->redirect('products');
        }

        if ($this->isPost()) {
            $data = [
                'name' => $this->sanitize($_POST['name'] ?? ''),
                'sku' => $this->sanitize($_POST['sku'] ?? ''),
                'description' => $this->sanitize($_POST['description'] ?? ''),
                'price' => $_POST['price'] ?? 0,
                'cost' => $_POST['cost'] ?? 0,
                'stock' => $_POST['stock'] ?? 0,
                'low_stock_alert' => $_POST['low_stock_alert'] ?? 10,
                'category_id' => $_POST['category_id'] ?? null,
                'is_active' => isset($_POST['is_active']) ? 1 : 0,
            ];

            $this->productModel->update((int)$id, $data);
            $this->setFlash('success', 'Product updated successfully!');
            $this->redirect('products');
        }

        $categories = $this->categoryModel->findAll('', 'name ASC');
        $this->view('products/form', [
            'pageTitle' => 'Edit Product',
            'categories' => $categories,
            'product' => $product,
        ]);
    }

    public function delete($id): void {
        $this->requireAdmin();
        $this->productModel->update((int)$id, array_merge(
            $this->productModel->findById((int)$id) ?? [],
            ['is_active' => 0]
        ));
        $this->setFlash('success', 'Product deactivated.');
        $this->redirect('products');
    }

    // Categories
    public function categories(): void {
        $categories = $this->categoryModel->findAll('', 'name ASC');
        $this->view('products/categories', [
            'pageTitle' => 'Categories',
            'categories' => $categories,
            'flash' => $this->getFlash(),
        ]);
    }

    public function saveCategory(): void {
        $this->requireAdmin();
        if (!$this->isPost()) $this->redirect('products/categories');

        $id = (int)($_POST['id'] ?? 0);
        $data = [
            'name' => $this->sanitize($_POST['name'] ?? ''),
            'description' => $this->sanitize($_POST['description'] ?? ''),
        ];

        if ($id) {
            $this->categoryModel->update($id, $data);
            $this->setFlash('success', 'Category updated!');
        } else {
            $this->categoryModel->create($data);
            $this->setFlash('success', 'Category created!');
        }
        $this->redirect('products/categories');
    }

    public function deleteCategory($id): void {
        $this->requireAdmin();
        $this->categoryModel->delete((int)$id);
        $this->setFlash('success', 'Category deleted.');
        $this->redirect('products/categories');
    }
}