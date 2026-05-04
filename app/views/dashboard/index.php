<?php
$todaySales = number_format((float)($todaySummary['total_sales'] ?? 0), 2);
$todayTx = (int)($todaySummary['total_transactions'] ?? 0);
$todayTax = number_format((float)($todaySummary['total_tax'] ?? 0), 2);
$todayDiscount = number_format((float)($todaySummary['total_discount'] ?? 0), 2);
$avgTx = number_format((float)($todaySummary['avg_transaction'] ?? 0), 2);
?>

<div class="dashboard">
    <!-- Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card accent-green">
            <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
            <div class="stat-body">
                <div class="stat-value">₱<?= $todaySales ?></div>
                <div class="stat-label">Today's Sales</div>
            </div>
        </div>
        <div class="stat-card accent-blue">
            <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $todayTx ?></div>
                <div class="stat-label">Transactions Today</div>
            </div>
        </div>
        <div class="stat-card accent-orange">
            <div class="stat-icon"><i class="fas fa-box"></i></div>
            <div class="stat-body">
                <div class="stat-value"><?= $totalProducts ?></div>
                <div class="stat-label">Active Products</div>
            </div>
        </div>
        <div class="stat-card accent-purple">
            <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
            <div class="stat-body">
                <div class="stat-value">₱<?= $avgTx ?></div>
                <div class="stat-label">Avg. Transaction</div>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <!-- Recent Sales -->
        <div class="card span-2">
            <div class="card-header">
                <h3><i class="fas fa-receipt"></i> Recent Transactions</h3>
                <a href="<?= BASE_URL ?>/index.php?url=sales/history" class="btn btn-sm">View All</a>
            </div>
            <div class="card-body p-0">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Invoice</th>
                            <th>Customer</th>
                            <th>Cashier</th>
                            <th>Total</th>
                            <th>Payment</th>
                            <th>Status</th>
                            <th>Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentSales)): ?>
                        <tr><td colspan="7" class="text-center text-muted">No sales today</td></tr>
                        <?php else: ?>
                        <?php foreach ($recentSales as $sale): ?>
                        <tr>
                            <td><a href="<?= BASE_URL ?>/index.php?url=sales/show&id=<?= $sale['id'] ?>" class="link"><?= htmlspecialchars($sale['invoice_number']) ?></a></td>
                            <td><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></td>
                            <td><?= htmlspecialchars($sale['cashier_name']) ?></td>
                            <td><strong>₱<?= number_format($sale['total_amount'], 2) ?></strong></td>
                            <td><span class="badge badge-<?= $sale['payment_method'] ?>"><?= ucfirst($sale['payment_method']) ?></span></td>
                            <td><span class="badge badge-<?= $sale['status'] ?>"><?= ucfirst($sale['status']) ?></span></td>
                            <td class="text-muted"><?= date('h:i A', strtotime($sale['created_at'])) ?></td>
                        </tr>
                        <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Low Stock Alert -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-exclamation-triangle text-orange"></i> Low Stock Alert</h3>
                <a href="<?= BASE_URL ?>/index.php?url=products" class="btn btn-sm">Manage</a>
            </div>
            <div class="card-body p-0">
                <?php if (empty($lowStock)): ?>
                <div class="empty-state small"><i class="fas fa-check-circle text-green"></i><p>All stock levels OK</p></div>
                <?php else: ?>
                <div class="stock-list">
                    <?php foreach ($lowStock as $item): ?>
                    <div class="stock-item">
                        <div class="stock-info">
                            <span class="stock-name"><?= htmlspecialchars($item['name']) ?></span>
                            <span class="stock-sku"><?= htmlspecialchars($item['sku']) ?></span>
                        </div>
                        <div class="stock-badge <?= $item['stock'] == 0 ? 'out' : 'low' ?>">
                            <?= $item['stock'] == 0 ? 'OUT' : $item['stock'] . ' left' ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Top Products -->
        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-trophy text-orange"></i> Top Products</h3>
            </div>
            <div class="card-body p-0">
                <?php if (empty($topProducts)): ?>
                <div class="empty-state small"><p>No sales data yet</p></div>
                <?php else: ?>
                <div class="top-products">
                    <?php foreach ($topProducts as $i => $prod): ?>
                    <div class="top-product-item">
                        <span class="rank">#<?= $i+1 ?></span>
                        <div class="prod-info">
                            <span class="prod-name"><?= htmlspecialchars($prod['product_name']) ?></span>
                            <span class="prod-qty"><?= $prod['total_qty'] ?> sold</span>
                        </div>
                        <span class="prod-revenue">₱<?= number_format($prod['total_revenue'], 2) ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Quick Action -->
    <div class="quick-action">
        <a href="<?= BASE_URL ?>/index.php?url=sales" class="btn btn-primary btn-lg">
            <i class="fas fa-cash-register"></i> Open POS Terminal
        </a>
    </div>
</div>
