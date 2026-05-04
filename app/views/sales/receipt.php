<div class="receipt-page">
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/index.php?url=sales/history" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
        <button onclick="window.print()" class="btn btn-primary"><i class="fas fa-print"></i> Print</button>
    </div>

    <div class="receipt-doc" id="printArea">
        <div class="receipt-doc-header">
            <h2><?= APP_NAME ?></h2>
            <p>Official Receipt</p>
        </div>

        <div class="receipt-meta">
            <div><strong>Invoice:</strong> <?= htmlspecialchars($sale['invoice_number']) ?></div>
            <div><strong>Date:</strong> <?= date('F d, Y h:i A', strtotime($sale['created_at'])) ?></div>
            <div><strong>Cashier:</strong> <?= htmlspecialchars($sale['cashier_name']) ?></div>
            <div><strong>Customer:</strong> <?= htmlspecialchars($sale['customer_name'] ?? 'Walk-in Customer') ?></div>
            <div><strong>Payment:</strong> <?= ucfirst($sale['payment_method']) ?></div>
            <div><strong>Status:</strong> <span class="badge badge-<?= $sale['status'] ?>"><?= ucfirst($sale['status']) ?></span></div>
        </div>

        <table class="table receipt-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Unit Price</th>
                    <th>Qty</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sale['items'] as $i => $item): ?>
                <tr>
                    <td><?= $i+1 ?></td>
                    <td><?= htmlspecialchars($item['product_name']) ?></td>
                    <td>₱<?= number_format($item['unit_price'], 2) ?></td>
                    <td><?= $item['quantity'] ?></td>
                    <td>₱<?= number_format($item['subtotal'], 2) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="receipt-doc-totals">
            <div class="rt-row"><span>Subtotal</span><span>₱<?= number_format($sale['subtotal'], 2) ?></span></div>
            <?php if ($sale['discount_amount'] > 0): ?>
            <div class="rt-row"><span>Discount</span><span>-₱<?= number_format($sale['discount_amount'], 2) ?></span></div>
            <?php endif; ?>
            <div class="rt-row"><span>Tax (<?= $sale['tax_rate'] ?>%)</span><span>₱<?= number_format($sale['tax_amount'], 2) ?></span></div>
            <div class="rt-row rt-final"><span>TOTAL</span><span>₱<?= number_format($sale['total_amount'], 2) ?></span></div>
            <div class="rt-row"><span>Amount Paid</span><span>₱<?= number_format($sale['amount_paid'], 2) ?></span></div>
            <div class="rt-row"><span>Change</span><span>₱<?= number_format($sale['change_amount'], 2) ?></span></div>
        </div>

        <?php if ($sale['notes']): ?>
        <div class="receipt-notes"><strong>Notes:</strong> <?= htmlspecialchars($sale['notes']) ?></div>
        <?php endif; ?>

        <div class="receipt-doc-footer">Thank you for your business!</div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .page-actions { display: none !important; }
    .main-content { padding: 0 !important; }
    .receipt-doc { box-shadow: none !important; max-width: 100% !important; }
}
</style>
