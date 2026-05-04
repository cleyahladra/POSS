<div class="dashboard-grid">
    <div class="card span-2">
        <div class="card-header">
            <h3><i class="fas fa-trophy text-orange"></i> Top Selling Products</h3>
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr><th>#</th><th>Product</th><th>Total Sold</th><th>Revenue</th><th>Revenue Bar</th></tr>
                </thead>
                <tbody>
                    <?php
                    $maxRev = max(array_column($topProducts ?: [['total_revenue' => 1]], 'total_revenue') ?: [1]);
                    foreach ($topProducts as $i => $p):
                        $pct = $maxRev > 0 ? ($p['total_revenue'] / $maxRev * 100) : 0;
                    ?>
                    <tr>
                        <td><strong>#<?= $i+1 ?></strong></td>
                        <td><?= htmlspecialchars($p['product_name']) ?></td>
                        <td><?= $p['total_qty'] ?> units</td>
                        <td><strong>₱<?= number_format($p['total_revenue'], 2) ?></strong></td>
                        <td><div class="bar-track"><div class="bar-fill" style="width:<?= $pct ?>%"></div></div></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($topProducts)): ?>
                    <tr><td colspan="5" class="text-center text-muted">No sales data yet.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-exclamation-triangle text-orange"></i> Low / Out of Stock</h3>
        </div>
        <div class="card-body p-0">
            <?php if (empty($lowStock)): ?>
            <div class="empty-state small"><i class="fas fa-check-circle text-green"></i><p>All levels OK</p></div>
            <?php else: ?>
            <table class="table">
                <thead><tr><th>Product</th><th>SKU</th><th>Stock</th><th>Min</th></tr></thead>
                <tbody>
                    <?php foreach ($lowStock as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><code><?= htmlspecialchars($p['sku']) ?></code></td>
                        <td><span class="stock-pill <?= $p['stock'] <= 0 ? 'out' : 'low' ?>"><?= $p['stock'] ?></span></td>
                        <td><?= $p['low_stock_alert'] ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
