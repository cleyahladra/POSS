<?php
$categorized = [];
foreach ($products as $p) {
    $cat = $p['category_name'] ?? 'Uncategorized';
    $categorized[$cat][] = $p;
}
?>
<div class="pos-layout">
    <!-- Products Panel -->
    <div class="pos-products">
        <div class="pos-search-bar">
            <div class="input-icon">
                <i class="fas fa-search"></i>
                <input type="text" id="productSearch" placeholder="Search product or scan barcode..." autocomplete="off">
            </div>
        </div>

        <div class="category-tabs" id="categoryTabs">
            <button class="cat-tab active" data-cat="all">All</button>
            <?php foreach (array_keys($categorized) as $cat): ?>
            <button class="cat-tab" data-cat="<?= htmlspecialchars($cat) ?>"><?= htmlspecialchars($cat) ?></button>
            <?php endforeach; ?>
        </div>

        <div class="product-grid" id="productGrid">
            <?php foreach ($products as $product): ?>
            <div class="product-card <?= $product['stock'] <= 0 ? 'out-of-stock' : '' ?>"
                 data-id="<?= $product['id'] ?>"
                 data-name="<?= htmlspecialchars($product['name']) ?>"
                 data-price="<?= $product['price'] ?>"
                 data-sku="<?= htmlspecialchars($product['sku']) ?>"
                 data-stock="<?= $product['stock'] ?>"
                 data-cat="<?= htmlspecialchars($product['category_name'] ?? '') ?>"
                 onclick="addToCart(this)">
                <div class="product-card-body">
                    <div class="product-icon"><i class="fas fa-box-open"></i></div>
                    <div class="product-name"><?= htmlspecialchars($product['name']) ?></div>
                    <div class="product-sku"><?= htmlspecialchars($product['sku']) ?></div>
                    <div class="product-price">₱<?= number_format($product['price'], 2) ?></div>
                    <div class="product-stock <?= $product['stock'] <= $product['low_stock_alert'] ? 'low' : '' ?>">
                        <?= $product['stock'] <= 0 ? '✗ Out of stock' : 'Stock: ' . $product['stock'] ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart">
        <div class="cart-header">
            <h2><i class="fas fa-shopping-cart"></i> Current Order</h2>
            <button class="btn btn-sm btn-danger" onclick="clearCart()"><i class="fas fa-trash"></i> Clear</button>
        </div>

        <!-- Customer Select -->
        <div class="customer-select">
            <div class="input-icon">
                <i class="fas fa-user"></i>
                <select id="customerSelect" name="customer_id">
                    <option value="">Walk-in Customer</option>
                    <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>"><?= htmlspecialchars($c['name']) ?><?= $c['phone'] ? ' — ' . $c['phone'] : '' ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <!-- Cart Items -->
        <div class="cart-items" id="cartItems">
            <div class="cart-empty" id="cartEmpty">
                <i class="fas fa-shopping-basket"></i>
                <p>Cart is empty</p>
                <small>Click products to add them</small>
            </div>
        </div>

        <!-- Cart Totals -->
        <div class="cart-totals">
            <div class="total-row">
                <span>Subtotal</span>
                <span id="subtotal">₱0.00</span>
            </div>
            <div class="total-row">
                <span>Discount</span>
                <div class="discount-input">
                    <span>₱</span>
                    <input type="number" id="discountAmount" value="0" min="0" step="0.01" onchange="recalculate()">
                </div>
            </div>
            <div class="total-row">
                <span>Tax (<?= TAX_RATE ?>%)</span>
                <span id="taxAmount">₱0.00</span>
            </div>
            <div class="total-row total-final">
                <span>TOTAL</span>
                <span id="totalAmount">₱0.00</span>
            </div>
        </div>

        <!-- Payment -->
        <div class="payment-section">
            <div class="payment-methods">
                <button class="pay-method active" data-method="cash" onclick="setPayment('cash', this)">
                    <i class="fas fa-money-bill-wave"></i> Cash
                </button>
                <button class="pay-method" data-method="card" onclick="setPayment('card', this)">
                    <i class="fas fa-credit-card"></i> Card
                </button>
                <button class="pay-method" data-method="gcash" onclick="setPayment('gcash', this)">
                    <i class="fas fa-mobile-alt"></i> GCash
                </button>
            </div>

            <div id="cashInputGroup" class="cash-input-group">
                <label>Amount Tendered</label>
                <input type="number" id="amountPaid" placeholder="0.00" min="0" step="0.01" oninput="calcChange()">
                <div class="change-display">
                    <span>Change:</span>
                    <span id="changeAmount" class="change-value">₱0.00</span>
                </div>
                <div class="quick-cash">
                    <button onclick="setExactCash()">Exact</button>
                    <button onclick="setQuickCash(500)">₱500</button>
                    <button onclick="setQuickCash(1000)">₱1000</button>
                </div>
            </div>

            <button class="btn-checkout" id="checkoutBtn" onclick="processCheckout()">
                <i class="fas fa-check-circle"></i> Process Payment
            </button>
        </div>
    </div>
</div>

<!-- Receipt Modal -->
<div class="modal-overlay" id="receiptModal" style="display:none;">
    <div class="modal receipt-modal">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle text-green"></i> Sale Completed!</h3>
        </div>
        <div class="receipt-content" id="receiptContent"></div>
        <div class="modal-footer">
            <button class="btn btn-outline" onclick="printReceipt()"><i class="fas fa-print"></i> Print</button>
            <button class="btn btn-primary" onclick="newTransaction()"><i class="fas fa-plus"></i> New Sale</button>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const TAX_RATE = <?= TAX_RATE ?>;
let cart = [];
let paymentMethod = 'cash';
let currentSale = null;

function addToCart(el) {
    const id = el.dataset.id;
    const stock = parseInt(el.dataset.stock);
    if (stock <= 0) return;

    const existing = cart.find(i => i.id === id);
    if (existing) {
        if (existing.qty >= stock) { alert('Cannot exceed stock quantity!'); return; }
        existing.qty++;
    } else {
        cart.push({
            id: id,
            name: el.dataset.name,
            price: parseFloat(el.dataset.price),
            sku: el.dataset.sku,
            stock: stock,
            qty: 1,
            discount: 0
        });
    }
    renderCart();
}

function renderCart() {
    const container = document.getElementById('cartItems');
    const empty = document.getElementById('cartEmpty');

    if (cart.length === 0) {
        empty.style.display = 'flex';
        container.innerHTML = '';
        container.appendChild(empty);
        recalculate();
        return;
    }

    empty.style.display = 'none';
    let html = '';
    cart.forEach((item, idx) => {
        const sub = item.price * item.qty;
        html += `
        <div class="cart-item">
            <div class="ci-info">
                <span class="ci-name">${item.name}</span>
                <span class="ci-sku">${item.sku}</span>
            </div>
            <div class="ci-controls">
                <button class="ci-btn" onclick="changeQty(${idx}, -1)">−</button>
                <span class="ci-qty">${item.qty}</span>
                <button class="ci-btn" onclick="changeQty(${idx}, 1)">+</button>
            </div>
            <div class="ci-price">
                <span>₱${item.price.toFixed(2)}</span>
                <strong>₱${sub.toFixed(2)}</strong>
            </div>
            <button class="ci-remove" onclick="removeItem(${idx})"><i class="fas fa-times"></i></button>
        </div>`;
    });

    container.innerHTML = html;
    recalculate();
}

function changeQty(idx, delta) {
    cart[idx].qty += delta;
    if (cart[idx].qty <= 0) cart.splice(idx, 1);
    else if (cart[idx].qty > cart[idx].stock) cart[idx].qty = cart[idx].stock;
    renderCart();
}

function removeItem(idx) {
    cart.splice(idx, 1);
    renderCart();
}

function clearCart() {
    if (cart.length && !confirm('Clear all items?')) return;
    cart = [];
    renderCart();
}

function recalculate() {
    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const taxable = subtotal - discount;
    const tax = taxable * (TAX_RATE / 100);
    const total = taxable + tax;

    document.getElementById('subtotal').textContent = '₱' + subtotal.toFixed(2);
    document.getElementById('taxAmount').textContent = '₱' + tax.toFixed(2);
    document.getElementById('totalAmount').textContent = '₱' + total.toFixed(2);
    calcChange();
}

function calcChange() {
    const total = parseFloat(document.getElementById('totalAmount').textContent.replace('₱', '')) || 0;
    const paid = parseFloat(document.getElementById('amountPaid').value) || 0;
    const change = paid - total;
    document.getElementById('changeAmount').textContent = '₱' + Math.max(0, change).toFixed(2);
    document.getElementById('changeAmount').style.color = change < 0 ? '#e74c3c' : '#2ecc71';
}

function setExactCash() {
    const total = document.getElementById('totalAmount').textContent.replace('₱', '');
    document.getElementById('amountPaid').value = total;
    calcChange();
}

function setQuickCash(amount) {
    document.getElementById('amountPaid').value = amount;
    calcChange();
}

function setPayment(method, btn) {
    paymentMethod = method;
    document.querySelectorAll('.pay-method').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById('cashInputGroup').style.display = method === 'cash' ? 'block' : 'none';
}

async function processCheckout() {
    if (cart.length === 0) { alert('Cart is empty!'); return; }

    const subtotal = cart.reduce((s, i) => s + i.price * i.qty, 0);
    const discount = parseFloat(document.getElementById('discountAmount').value) || 0;
    const tax = (subtotal - discount) * (TAX_RATE / 100);
    const total = (subtotal - discount) + tax;
    const paid = paymentMethod === 'cash' ? (parseFloat(document.getElementById('amountPaid').value) || 0) : total;

    if (paymentMethod === 'cash' && paid < total) { alert('Insufficient payment!'); return; }

    const items = cart.map(i => ({
        product_id: i.id,
        product_name: i.name,
        quantity: i.qty,
        unit_price: i.price,
        discount: 0,
        subtotal: i.price * i.qty
    }));

    const payload = {
        customer_id: document.getElementById('customerSelect').value || null,
        items,
        subtotal,
        tax_rate: TAX_RATE,
        tax_amount: tax,
        discount_amount: discount,
        total_amount: total,
        amount_paid: paid,
        change_amount: Math.max(0, paid - total),
        payment_method: paymentMethod,
    };

    document.getElementById('checkoutBtn').disabled = true;
    document.getElementById('checkoutBtn').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

    try {
        const res = await fetch(BASE_URL + '/index.php?url=sales/process', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            currentSale = data.sale;
            showReceipt(data.sale, paid, Math.max(0, paid - total));
        } else {
            alert('Error: ' + (data.message || 'Failed to process sale'));
        }
    } catch (e) {
        alert('Network error. Please try again.');
    } finally {
        document.getElementById('checkoutBtn').disabled = false;
        document.getElementById('checkoutBtn').innerHTML = '<i class="fas fa-check-circle"></i> Process Payment';
    }
}

function showReceipt(sale, paid, change) {
    let itemsHtml = sale.items.map(i => `
        <div class="r-item">
            <span>${i.product_name} x${i.quantity}</span>
            <span>₱${parseFloat(i.subtotal).toFixed(2)}</span>
        </div>`).join('');

    document.getElementById('receiptContent').innerHTML = `
        <div class="receipt-header">
            <strong>${'<?= APP_NAME ?>'}</strong>
            <div>${sale.invoice_number}</div>
            <div style="font-size:12px;color:#888">${new Date().toLocaleString()}</div>
        </div>
        <div class="receipt-items">${itemsHtml}</div>
        <div class="receipt-totals">
            <div class="r-row"><span>Subtotal</span><span>₱${parseFloat(sale.subtotal).toFixed(2)}</span></div>
            <div class="r-row"><span>Discount</span><span>-₱${parseFloat(sale.discount_amount).toFixed(2)}</span></div>
            <div class="r-row"><span>Tax (${TAX_RATE}%)</span><span>₱${parseFloat(sale.tax_amount).toFixed(2)}</span></div>
            <div class="r-row r-total"><span>TOTAL</span><span>₱${parseFloat(sale.total_amount).toFixed(2)}</span></div>
            <div class="r-row"><span>Paid (${sale.payment_method})</span><span>₱${paid.toFixed(2)}</span></div>
            <div class="r-row r-change"><span>Change</span><span>₱${change.toFixed(2)}</span></div>
        </div>
        <div class="receipt-footer">Thank you for your purchase!</div>`;

    document.getElementById('receiptModal').style.display = 'flex';
}

function printReceipt() {
    const content = document.getElementById('receiptContent').innerHTML;
    const win = window.open('', '_blank', 'width=380,height=600');
    win.document.write(`<!DOCTYPE html><html><head><title>Receipt</title>
    <style>body{font-family:monospace;font-size:13px;max-width:380px;margin:0 auto;padding:16px}
    .receipt-header{text-align:center;margin-bottom:12px;border-bottom:1px dashed #ccc;padding-bottom:8px}
    .r-item,.r-row{display:flex;justify-content:space-between;padding:2px 0}
    .receipt-items{border-bottom:1px dashed #ccc;padding-bottom:8px;margin-bottom:8px}
    .r-total{font-weight:bold;border-top:1px dashed #ccc;padding-top:4px;margin-top:4px}
    .receipt-footer{text-align:center;margin-top:12px;border-top:1px dashed #ccc;padding-top:8px}
    </style></head><body>${content}</body></html>`);
    win.document.close();
    win.print();
}

function newTransaction() {
    cart = [];
    document.getElementById('discountAmount').value = 0;
    document.getElementById('amountPaid').value = '';
    document.getElementById('customerSelect').value = '';
    renderCart();
    document.getElementById('receiptModal').style.display = 'none';
}

// Product search & filter
document.getElementById('productSearch').addEventListener('input', function() {
    const q = this.value.toLowerCase();
    document.querySelectorAll('.product-card').forEach(card => {
        const match = card.dataset.name.toLowerCase().includes(q) || card.dataset.sku.toLowerCase().includes(q);
        card.style.display = match ? '' : 'none';
    });
});

document.getElementById('categoryTabs').addEventListener('click', function(e) {
    if (!e.target.classList.contains('cat-tab')) return;
    document.querySelectorAll('.cat-tab').forEach(t => t.classList.remove('active'));
    e.target.classList.add('active');
    const cat = e.target.dataset.cat;
    document.querySelectorAll('.product-card').forEach(card => {
        card.style.display = (cat === 'all' || card.dataset.cat === cat) ? '' : 'none';
    });
});

renderCart();
</script>
