<div class="page-toolbar">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="url" value="sales/history">
        <div class="filter-group">
            <label>From</label>
            <input type="date" name="date_from" value="<?= htmlspecialchars($filters['date_from']) ?>">
        </div>
        <div class="filter-group">
            <label>To</label>
            <input type="date" name="date_to" value="<?= htmlspecialchars($filters['date_to']) ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="status">
                <option value="">All</option>
                <option value="completed" <?= $filters['status']==='completed'?'selected':'' ?>>Completed</option>
                <option value="voided" <?= $filters['status']==='voided'?'selected':'' ?>>Voided</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-filter"></i> Filter</button>
        <a href="<?= BASE_URL ?>/index.php?url=sales/history" class="btn btn-outline">Reset</a>
    </form>
    <a href="<?= BASE_URL ?>/index.php?url=sales" class="btn btn-primary"><i class="fas fa-plus"></i> New Sale</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Sales Transactions (<?= count($sales) ?> records)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>Invoice #</th>
                    <th>Date & Time</th>
                    <th>Customer</th>
                    <th>Cashier</th>
                    <th>Subtotal</th>
                    <th>Tax</th>
                    <th>Discount</th>
                    <th>Total</th>
                    <th>Payment</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($sales)): ?>
                <tr><td colspan="11" class="text-center text-muted">No transactions found.</td></tr>
                <?php else: ?>
                <?php foreach ($sales as $sale): ?>
                <tr class="<?= $sale['status'] === 'voided' ? 'row-voided' : '' ?>">
                    <td><strong><?= htmlspecialchars($sale['invoice_number']) ?></strong></td>
                    <td><?= date('M d, Y h:i A', strtotime($sale['created_at'])) ?></td>
                    <td><?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in') ?></td>
                    <td><?= htmlspecialchars($sale['cashier_name']) ?></td>
                    <td>₱<?= number_format($sale['subtotal'], 2) ?></td>
                    <td>₱<?= number_format($sale['tax_amount'], 2) ?></td>
                    <td>₱<?= number_format($sale['discount_amount'], 2) ?></td>
                    <td><strong>₱<?= number_format($sale['total_amount'], 2) ?></strong></td>
                    <td><span class="badge badge-<?= $sale['payment_method'] ?>"><?= ucfirst($sale['payment_method']) ?></span></td>
                    <td><span class="badge badge-<?= $sale['status'] ?>"><?= ucfirst($sale['status']) ?></span></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?url=sales/view/<?= $sale['id'] ?>" class="btn btn-sm btn-outline" title="View"><i class="fas fa-eye"></i></a>
                        <?php if ($sale['status'] === 'completed' && ($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/index.php?url=sales/void/<?= $sale['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Void this sale? Stock will be restored.')" title="Void"><i class="fas fa-ban"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php if (!empty($sales)): ?>
<div class="summary-bar">
    <?php
    $completed = array_filter($sales, fn($s) => $s['status'] === 'completed');
    $totalRevenue = array_sum(array_column($completed, 'total_amount'));
    ?>
    <span><strong><?= count($completed) ?></strong> completed transactions</span>
    <span>Total Revenue: <strong>₱<?= number_format($totalRevenue, 2) ?></strong></span>
</div>
<?php endif; ?>
