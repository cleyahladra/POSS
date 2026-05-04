<div class="page-toolbar">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="url" value="reports">
        <div class="filter-group">
            <label>Date</label>
            <input type="date" name="date" value="<?= htmlspecialchars($date) ?>">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-search"></i> View</button>
    </form>
</div>

<div class="stats-grid">
    <div class="stat-card accent-green">
        <div class="stat-icon"><i class="fas fa-peso-sign"></i></div>
        <div class="stat-body">
            <div class="stat-value">₱<?= number_format($summary['total_sales'] ?? 0, 2) ?></div>
            <div class="stat-label">Total Revenue</div>
        </div>
    </div>
    <div class="stat-card accent-blue">
        <div class="stat-icon"><i class="fas fa-receipt"></i></div>
        <div class="stat-body">
            <div class="stat-value"><?= (int)($summary['total_transactions'] ?? 0) ?></div>
            <div class="stat-label">Transactions</div>
        </div>
    </div>
    <div class="stat-card accent-orange">
        <div class="stat-icon"><i class="fas fa-percent"></i></div>
        <div class="stat-body">
            <div class="stat-value">₱<?= number_format($summary['total_tax'] ?? 0, 2) ?></div>
            <div class="stat-label">Tax Collected</div>
        </div>
    </div>
    <div class="stat-card accent-purple">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-body">
            <div class="stat-value">₱<?= number_format($summary['avg_transaction'] ?? 0, 2) ?></div>
            <div class="stat-label">Avg Transaction</div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3>Transactions for <?= date('F d, Y', strtotime($date)) ?></h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr><th>Invoice</th><th>Customer</th><th>Cashier</th><th>Total</th><th>Payment</th><th>Time</th></tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr><td colspan="6" class="text-center text-muted">No sales for this date.</td></tr>
                <?php else: ?>
                <?php foreach ($sales as $s): ?>
                <tr>
                    <td><a href="<?= BASE_URL ?>/index.php?url=sales/view/<?= $s['id'] ?>" class="link"><?= $s['invoice_number'] ?></a></td>
                    <td><?= htmlspecialchars($s['customer_name'] ?? 'Walk-in') ?></td>
                    <td><?= htmlspecialchars($s['cashier_name']) ?></td>
                    <td><strong>₱<?= number_format($s['total_amount'], 2) ?></strong></td>
                    <td><?= ucfirst($s['payment_method']) ?></td>
                    <td><?= date('h:i A', strtotime($s['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
