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
                        <div class="product-price">&#8369;<?= number_format($product['price'], 2) ?></div>
                        <div class="product-stock <?= $product['stock'] <= $product['low_stock_alert'] ? 'low' : '' ?>">
                            <?= $product['stock'] <= 0 ? '&#x2717; Out of stock' : 'Stock: ' . $product['stock'] ?>
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
                <span id="subtotal">&#8369;0.00</span>
            </div>
            <div class="total-row">
                <span>Discount</span>
                <div class="discount-input">
                    <span>&#8369;</span>
                    <input type="number" id="discountAmount" value="0" min="0" step="0.01" oninput="recalculate()">
                </div>
            </div>
            <div class="total-row">
                <span>Tax (<?= TAX_RATE ?>%)</span>
                <span id="taxAmount">&#8369;0.00</span>
            </div>
            <div class="total-row total-final">
                <span>TOTAL</span>
                <span id="totalAmount">&#8369;0.00</span>
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
                <label style="margin-top:10px;">Change</label>
                <input type="number" id="changeAmount" placeholder="0.00" step="0.01" readonly
                       style="font-weight:700;font-size:16px;color:#2ecc71;background:rgba(46,204,113,0.08);border-color:rgba(46,204,113,0.25);">
                <div class="quick-cash">
                    <button onclick="setExactCash()">Exact</button>
                    <button onclick="setQuickCash(500)">&#8369;500</button>
                    <button onclick="setQuickCash(1000)">&#8369;1000</button>
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
    var BASE_URL = '<?= BASE_URL ?>';
    var TAX_RATE = <?= TAX_RATE ?>;
    var cart = [];
    var paymentMethod = 'cash';
    var currentSale = null;

    function addToCart(el) {
        var id = el.dataset.id;
        var stock = parseInt(el.dataset.stock);
        var originalStock = parseInt(el.dataset.originalStock || el.dataset.stock);

        if (stock <= 0) return;

        var existing = null;

        for (var i = 0; i < cart.length; i++) {
            if (cart[i].id === id) {
                existing = cart[i];
                break;
            }
        }

        if (existing) {

            if (existing.qty >= existing.stock) {
                alert('Cannot exceed available stock!');
                return;
            }

            existing.qty++;
            existing.element = el; // 🔥 ensure element is always stored

        } else {

            cart.push({
                id: id,
                name: el.dataset.name,
                price: parseFloat(el.dataset.price),
                sku: el.dataset.sku,
                qty: 1,
                stock: stock,
                originalStock: originalStock,
                element: el // 🔥 IMPORTANT
            });
        }

        stock--;
        el.dataset.stock = stock;

        updateProductStock(el, stock);

        renderCart();
    }

    function renderCart() {

        var cards = document.querySelectorAll('.product-card');

        // reset all highlights
        for (var i = 0; i < cards.length; i++) {
            cards[i].classList.remove('selected');
        }

        // re-apply highlight based on cart IDs (stable method)
        for (var i = 0; i < cart.length; i++) {
            var id = cart[i].id;

            var card = document.querySelector('.product-card[data-id="' + id + '"]');
            if (card) {
                card.classList.add('selected');
            }
        }

        var container = document.getElementById('cartItems');
        var empty = document.getElementById('cartEmpty');

        // remove only cart items
        var rows = container.querySelectorAll('.cart-item');
        for (var i = 0; i < rows.length; i++) {
            rows[i].remove();
        }

        // empty cart state
        if (cart.length === 0) {
            empty.style.display = 'flex';
            recalculate();
            return;
        }

        empty.style.display = 'none';

        // rebuild cart UI
        for (var idx = 0; idx < cart.length; idx++) {

            var item = cart[idx];
            var sub = item.price * item.qty;

            var div = document.createElement('div');
            div.className = 'cart-item';

            div.innerHTML =
                '<div class="ci-info">' +
                '<span class="ci-name">' + item.name + '</span>' +
                '<span class="ci-sku">' + item.sku + '</span>' +
                '</div>' +

                '<div class="ci-controls">' +
                '<button class="ci-btn" onclick="changeQty(' + idx + ',-1)">&#8722;</button>' +
                '<span class="ci-qty">' + item.qty + '</span>' +
                '<button class="ci-btn" onclick="changeQty(' + idx + ',1)">+</button>' +
                '</div>' +

                '<div class="ci-price">' +
                '<span>&#8369;' + item.price.toFixed(2) + '</span>' +
                '<strong>&#8369;' + sub.toFixed(2) + '</strong>' +
                '</div>' +

                '<button class="ci-remove" onclick="removeItem(' + idx + ')">' +
                '<i class="fas fa-times"></i>' +
                '</button>';

            container.appendChild(div);
        }

        recalculate();
    }

    function changeQty(idx, delta) {
        var item = cart[idx];
        var el = item.element;

        var currentStock = parseInt(el.dataset.stock);

        if (delta > 0) {
            if (item.qty >= item.originalStock) return;

            item.qty++;
            currentStock--;
        } else {
            item.qty--;
            currentStock++;
        }

        el.dataset.stock = currentStock;
        updateProductStock(el, currentStock);

        if (item.qty <= 0) {
            el.classList.remove('selected');
            cart.splice(idx, 1);
        }

        renderCart();
    }

    function clearCart() {
        if (cart.length > 0 && !confirm('Clear all items?')) return;
        cart = [];
        document.querySelectorAll('.product-card').forEach(function(card) {
            card.classList.remove('selected');
        });
        renderCart();
    }

    function recalculate() {
        var subtotal = 0;
        for (var i = 0; i < cart.length; i++) {
            subtotal += cart[i].price * cart[i].qty;
        }
        var discount = parseFloat(document.getElementById('discountAmount').value) || 0;
        var taxable = subtotal - discount;
        if (taxable < 0) taxable = 0;
        var tax = taxable * (TAX_RATE / 100);
        var total = taxable + tax;

        document.getElementById('subtotal').textContent = '\u20B1' + subtotal.toFixed(2);
        document.getElementById('taxAmount').textContent = '\u20B1' + tax.toFixed(2);
        document.getElementById('totalAmount').textContent = '\u20B1' + total.toFixed(2);
        calcChange();
    }

    function calcChange() {
        var totalText = document.getElementById('totalAmount').textContent.replace('\u20B1', '');
        var total = parseFloat(totalText) || 0;
        var paid = parseFloat(document.getElementById('amountPaid').value) || 0;
        var change = paid - total;
        var changeEl = document.getElementById('changeAmount');
        if (change < 0) {
            changeEl.value = '';
            changeEl.style.color = '#e74c3c';
            changeEl.style.borderColor = 'rgba(231,76,60,0.4)';
            changeEl.placeholder = 'Insufficient';
        } else {
            changeEl.value = change.toFixed(2);
            changeEl.style.color = '#2ecc71';
            changeEl.style.borderColor = 'rgba(46,204,113,0.25)';
        }
    }

    function setExactCash() {
        var totalText = document.getElementById('totalAmount').textContent.replace('\u20B1', '');
        document.getElementById('amountPaid').value = parseFloat(totalText).toFixed(2);
        calcChange();
    }

    function setQuickCash(amount) {
        document.getElementById('amountPaid').value = amount;
        calcChange();
    }

    function setPayment(method, btn) {
        paymentMethod = method;
        var btns = document.querySelectorAll('.pay-method');
        for (var i = 0; i < btns.length; i++) btns[i].classList.remove('active');
        btn.classList.add('active');
        document.getElementById('cashInputGroup').style.display = (method === 'cash') ? 'block' : 'none';
    }

    function processCheckout() {
        if (cart.length === 0) { alert('Cart is empty!'); return; }

        var subtotal = 0;
        for (var i = 0; i < cart.length; i++) subtotal += cart[i].price * cart[i].qty;

        var discount = parseFloat(document.getElementById('discountAmount').value) || 0;
        var taxable = subtotal - discount;
        if (taxable < 0) taxable = 0;
        var tax = taxable * (TAX_RATE / 100);
        var total = taxable + tax;
        var paid = (paymentMethod === 'cash') ? (parseFloat(document.getElementById('amountPaid').value) || 0) : total;

        if (paymentMethod === 'cash' && paid < total) {
            alert('Amount tendered is less than the total!');
            return;
        }

        var items = [];
        for (var i = 0; i < cart.length; i++) {
            items.push({
                product_id:   cart[i].id,
                product_name: cart[i].name,
                quantity:     cart[i].qty,
                unit_price:   cart[i].price,
                discount:     0,
                subtotal:     cart[i].price * cart[i].qty
            });
        }

        var payload = {
            customer_id:     document.getElementById('customerSelect').value || null,
            items:           items,
            subtotal:        subtotal,
            tax_rate:        TAX_RATE,
            tax_amount:      tax,
            discount_amount: discount,
            total_amount:    total,
            amount_paid:     paid,
            change_amount:   Math.max(0, paid - total),
            payment_method:  paymentMethod
        };

        var btn = document.getElementById('checkoutBtn');
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';

        fetch(BASE_URL + '/index.php?url=sales/process', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            body:    JSON.stringify(payload)
        })
            .then(function(res) { return res.json(); })
            .then(function(data) {
                if (data.success) {
                    currentSale = data.sale;
                    showReceipt(data.sale, paid, Math.max(0, paid - total));
                } else {
                    alert('Error: ' + (data.message || 'Failed to process sale'));
                }
            })
            .catch(function() {
                alert('Network error. Please try again.');
            })
            .finally(function() {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-check-circle"></i> Process Payment';
            });
    }

    function showReceipt(sale, paid, change) {
        var itemsHtml = '';
        for (var i = 0; i < sale.items.length; i++) {
            var it = sale.items[i];
            itemsHtml += '<div class="r-item"><span>' + it.product_name + ' x' + it.quantity + '</span><span>&#8369;' + parseFloat(it.subtotal).toFixed(2) + '</span></div>';
        }

        document.getElementById('receiptContent').innerHTML =
            '<div class="receipt-header">' +
            '<strong><?= APP_NAME ?></strong>' +
            '<div>' + sale.invoice_number + '</div>' +
            '<div style="font-size:12px;color:#888">' + new Date().toLocaleString() + '</div>' +
            '</div>' +
            '<div class="receipt-items">' + itemsHtml + '</div>' +
            '<div class="receipt-totals">' +
            '<div class="r-row"><span>Subtotal</span><span>&#8369;' + parseFloat(sale.subtotal).toFixed(2) + '</span></div>' +
            '<div class="r-row"><span>Discount</span><span>-&#8369;' + parseFloat(sale.discount_amount).toFixed(2) + '</span></div>' +
            '<div class="r-row"><span>Tax (' + TAX_RATE + '%)</span><span>&#8369;' + parseFloat(sale.tax_amount).toFixed(2) + '</span></div>' +
            '<div class="r-row r-total"><span>TOTAL</span><span>&#8369;' + parseFloat(sale.total_amount).toFixed(2) + '</span></div>' +
            '<div class="r-row"><span>Paid (' + sale.payment_method + ')</span><span>&#8369;' + paid.toFixed(2) + '</span></div>' +
            '<div class="r-row r-change"><span>Change</span><span>&#8369;' + change.toFixed(2) + '</span></div>' +
            '</div>' +
            '<div class="receipt-footer">Thank you for your purchase!</div>';

        document.getElementById('receiptModal').style.display = 'flex';
    }

    function printReceipt() {
        var content = document.getElementById('receiptContent').innerHTML;
        var win = window.open('', '_blank', 'width=380,height=600');
        win.document.write('<!DOCTYPE html><html><head><title>Receipt</title>' +
            '<style>body{font-family:monospace;font-size:13px;max-width:380px;margin:0 auto;padding:16px}' +
            '.receipt-header{text-align:center;margin-bottom:12px;border-bottom:1px dashed #ccc;padding-bottom:8px}' +
            '.r-item,.r-row{display:flex;justify-content:space-between;padding:2px 0}' +
            '.receipt-items{border-bottom:1px dashed #ccc;padding-bottom:8px;margin-bottom:8px}' +
            '.r-total{font-weight:bold;border-top:1px dashed #ccc;padding-top:4px;margin-top:4px}' +
            '.receipt-footer{text-align:center;margin-top:12px;border-top:1px dashed #ccc;padding-top:8px}' +
            '</style></head><body>' + content + '</body></html>');
        win.document.close();
        win.print();
    }

    function newTransaction() {
        cart = [];
        document.getElementById('discountAmount').value = 0;
        document.getElementById('amountPaid').value = '';
        document.getElementById('changeAmount').value = '';
        document.getElementById('customerSelect').value = '';
        renderCart();
        document.getElementById('receiptModal').style.display = 'none';
    }

    // Product search
    document.getElementById('productSearch').addEventListener('input', function() {
        var q = this.value.toLowerCase();
        var cards = document.querySelectorAll('.product-card');
        for (var i = 0; i < cards.length; i++) {
            var match = cards[i].dataset.name.toLowerCase().indexOf(q) !== -1 ||
                cards[i].dataset.sku.toLowerCase().indexOf(q) !== -1;
            cards[i].style.display = match ? '' : 'none';
        }
    });

    // Category filter
    document.getElementById('categoryTabs').addEventListener('click', function(e) {
        if (!e.target.classList.contains('cat-tab')) return;
        var tabs = document.querySelectorAll('.cat-tab');
        for (var i = 0; i < tabs.length; i++) tabs[i].classList.remove('active');
        e.target.classList.add('active');
        var cat = e.target.dataset.cat;
        var cards = document.querySelectorAll('.product-card');
        for (var i = 0; i < cards.length; i++) {
            cards[i].style.display = (cat === 'all' || cards[i].dataset.cat === cat) ? '' : 'none';
        }
    });

    function updateProductStock(el, stock) {
        var stockDiv = el.querySelector('.product-stock');

        if (stock <= 0) {
            stockDiv.innerHTML = '&#x2717; Out of stock';
            el.classList.add('out-of-stock');
        } else {
            stockDiv.innerHTML = 'Stock: ' + stock;
            el.classList.remove('out-of-stock');
        }
    }

    function removeItem(idx) {
        var item = cart[idx];
        var el = item.element;

        var currentStock = parseInt(el.dataset.stock);
        currentStock += item.qty;

        el.dataset.stock = currentStock;

        updateProductStock(el, currentStock);
        el.classList.remove('selected');

        cart.splice(idx, 1);

        renderCart();
    }

    renderCart();
</script>
