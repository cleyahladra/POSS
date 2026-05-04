<?php
require_once __DIR__ . '/Controller.php';
require_once __DIR__ . '/../models/SaleModel.php';
require_once __DIR__ . '/../models/ProductModel.php';
require_once __DIR__ . '/../models/CustomerModel.php';

class DashboardController extends Controller {
    public function index(): void {
        $saleModel = new SaleModel();
        $productModel = new ProductModel();

        $todaySummary = $saleModel->getDailySummary();
        $recentSales = $saleModel->findAllWithDetails(['limit' => 8]);
        $lowStock = $productModel->getLowStock();
        $topProducts = $saleModel->getTopProducts(5);
        $monthlySales = $saleModel->getMonthlySales();
        $totalProducts = $productModel->count('is_active = 1');

        $this->view('dashboard/index', [
            'pageTitle' => 'Dashboard',
            'todaySummary' => $todaySummary,
            'recentSales' => $recentSales,
            'lowStock' => $lowStock,
            'topProducts' => $topProducts,
            'monthlySales' => $monthlySales,
            'totalProducts' => $totalProducts,
            'flash' => $this->getFlash(),
        ]);
    }
}
