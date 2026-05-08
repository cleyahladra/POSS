<?php
$categorized = [];

foreach ($products as $p) {
    $cat = $p['category_name'] ?? 'Uncategorized';
    $categorized[$cat][] = $p;
}
?>

<style>
    .product-card.selected {
        border: 2px solid #3498db;
        background: rgba(52, 152, 219, 0.08);
        transform: scale(0.98);
        transition: all 0.2s ease;
    }

    .product-card.out-of-stock {
        opacity: 0.6;
        pointer-events: none;
    }

    .product-stock.low {
        color: #e67e22;
        font-weight: 600;
    }
</style>

<div class="pos-layout">

    <!-- Products Panel -->
    <div class="pos-products">

        <div class="pos-search-bar">
            <div class="input-icon">
                <i class="fas fa-search"></i>

                <input
                        type="text"
                        id="productSearch"
                        placeholder="Search product or scan barcode..."
                        autocomplete="off">
            </div>
        </div>

        <div class="category-tabs" id="categoryTabs">

            <button class="cat-tab active" data-cat="all">
                All
            </button>

            <?php foreach (array_keys($categorized) as $cat): ?>

                <button
                        class="cat-tab"
                        data-cat="<?= htmlspecialchars($cat) ?>">

                    <?= htmlspecialchars($cat) ?>

                </button>

            <?php endforeach; ?>

        </div>

        <div class="product-grid" id="productGrid">

            <?php foreach ($products as $product): ?>

                <div
                        class="product-card <?= $product['stock'] <= 0 ? 'out-of-stock' : '' ?>"
                        data-id="<?= $product['id'] ?>"
                        data-name="<?= htmlspecialchars($product['name']) ?>"
                        data-price="<?= $product['price'] ?>"
                        data-sku="<?= htmlspecialchars($product['sku']) ?>"
                        data-stock="<?= $product['stock'] ?>"
                        data-original-stock="<?= $product['stock'] ?>"
                        data-cat="<?= htmlspecialchars($product['category_name'] ?? '') ?>"
                        onclick="addToCart(this)">

                    <div class="product-card-body">

                        <div class="product-icon">
                            <i class="fas fa-box-open"></i>
                        </div>

                        <div class="product-name">
                            <?= htmlspecialchars($product['name']) ?>
                        </div>

                        <div class="product-sku">
                            <?= htmlspecialchars($product['sku']) ?>
                        </div>

                        <div class="product-price">
                            &#8369;<?= number_format($product['price'], 2) ?>
                        </div>

                        <div class="product-stock <?= $product['stock'] <= $product['low_stock_alert'] ? 'low' : '' ?>">

                            <?= $product['stock'] <= 0
                                    ? '&#x2717; Out of stock'
                                    : 'Stock: ' . $product['stock'] ?>

                        </div>

                    </div>

                </div>

            <?php endforeach; ?>

        </div>
    </div>

    <!-- Cart Panel -->
    <div class="pos-cart">

        <div class="cart-header">
            <h2>
                <i class="fas fa-shopping-cart"></i>
                Current Order
            </h2>

            <button
                    class="btn btn-sm btn-danger"
                    onclick="clearCart()">

                <i class="fas fa-trash"></i>
                Clear

            </button>
        </div>

        <!-- Customer Select -->
        <div class="customer-select">

            <div class="input-icon">

                <i class="fas fa-user"></i>

                <select id="customerSelect" name="customer_id">

                    <option value="">
                        Walk-in Customer
                    </option>

                    <?php foreach ($customers as $c): ?>

                        <option value="<?= $c['id'] ?>">

                            <?= htmlspecialchars($c['name']) ?>

                            <?= $c['phone']
                                    ? ' — ' . $c['phone']
                                    : '' ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </div>
        </div>

        <!-- Cart Items -->
        <div class="cart-items" id="cartItems">

            <div class="cart-empty" id="cartEmpty">

                <i class="fas fa-shopping-basket"></i>

                <p>Cart is empty</p>

                <small>
                    Click products to add them
                </small>

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

                    <input
                            type="number"
                            id="discountAmount"
                            value="0"
                            min="0"
                            step="0.01"
                            oninput="recalculate()">

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

                <button
                        class="pay-method active"
                        data-method="cash"
                        onclick="setPayment('cash', this)">

                    <i class="fas fa-money-bill-wave"></i>
                    Cash

                </button>

                <button
                        class="pay-method"
                        data-method="card"
                        onclick="setPayment('card', this)">

                    <i class="fas fa-credit-card"></i>
                    Card

                </button>

                <button
                        class="pay-method"
                        data-method="gcash"
                        onclick="setPayment('gcash', this)">

                    <i class="fas fa-mobile-alt"></i>
                    GCash

                </button>

            </div>

            <div id="cashInputGroup" class="cash-input-group">

                <label>Amount Tendered</label>

                <input
                        type="number"
                        id="amountPaid"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                        oninput="calcChange()">

                <label style="margin-top:10px;">
                    Change
                </label>

                <input
                        type="number"
                        id="changeAmount"
                        placeholder="0.00"
                        step="0.01"
                        readonly>


            </div>

            <button
                    class="btn-checkout"
                    id="checkoutBtn"
                    onclick="processCheckout()">

                <i class="fas fa-check-circle"></i>
                Process Payment

            </button>

        </div>

    </div>

</div>

<script>
    var BASE_URL = '<?= BASE_URL ?>';
    var TAX_RATE = <?= TAX_RATE ?>;

    var cart = [];
    var paymentMethod = 'cash';

    function addToCart(el) {

        var id = el.dataset.id;

        var stock = parseInt(el.dataset.stock);

        if (stock <= 0) return;

        el.classList.add('selected');

        var existing = null;

        for (var i = 0; i < cart.length; i++) {

            if (cart[i].id === id) {
                existing = cart[i];
                break;
            }
        }

        if (existing) {

            if (existing.qty >= existing.originalStock) {
                alert('Cannot exceed available stock!');
                return;
            }

            existing.qty++;

        } else {

            cart.push({
                id: id,
                name: el.dataset.name,
                price: parseFloat(el.dataset.price),
                sku: el.dataset.sku,
                qty: 1,
                originalStock: parseInt(el.dataset.originalStock),
                element: el
            });
        }

        stock--;

        el.dataset.stock = stock;

        updateProductStock(el, stock);

        renderCart();
    }

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

    function renderCart() {

        var container = document.getElementById('cartItems');

        var empty = document.getElementById('cartEmpty');

        var rows = container.querySelectorAll('.cart-item');

        for (var i = 0; i < rows.length; i++) {
            rows[i].remove();
        }

        if (cart.length === 0) {

            empty.style.display = 'flex';

            recalculate();

            return;
        }

        empty.style.display = 'none';

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
                '<button class="ci-btn" onclick="changeQty(' + idx + ', -1)">−</button>' +
                '<span class="ci-qty">' + item.qty + '</span>' +
                '<button class="ci-btn" onclick="changeQty(' + idx + ', 1)">+</button>' +
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

        var currentStock =
            parseInt(item.element.dataset.stock);

        if (delta > 0) {

            if (item.qty >= item.originalStock) {
                alert('Cannot exceed available stock!');
                return;
            }

            item.qty++;

            currentStock--;

        } else {

            item.qty--;

            currentStock++;
        }

        item.element.dataset.stock = currentStock;

        updateProductStock(item.element, currentStock);

        if (item.qty <= 0) {

            item.element.classList.remove('selected');

            cart.splice(idx, 1);
        }

        renderCart();
    }

    function removeItem(idx) {

        var item = cart[idx];

        var currentStock =
            parseInt(item.element.dataset.stock);

        currentStock += item.qty;

        item.element.dataset.stock = currentStock;

        updateProductStock(item.element, currentStock);

        item.element.classList.remove('selected');

        cart.splice(idx, 1);

        renderCart();
    }

    function clearCart() {

        if (cart.length > 0 &&
            !confirm('Clear all items?')) return;

        for (var i = 0; i < cart.length; i++) {

            var item = cart[i];

            item.element.dataset.stock =
                item.originalStock;

            updateProductStock(
                item.element,
                item.originalStock
            );

            item.element.classList.remove('selected');
        }

        cart = [];

        renderCart();
    }

    function recalculate() {

        var subtotal = 0;

        for (var i = 0; i < cart.length; i++) {
            subtotal += cart[i].price * cart[i].qty;
        }

        var discount =
            parseFloat(
                document.getElementById('discountAmount').value
            ) || 0;

        var taxable = subtotal - discount;

        if (taxable < 0) taxable = 0;

        var tax = taxable * (TAX_RATE / 100);

        var total = taxable + tax;

        document.getElementById('subtotal').textContent =
            '₱' + subtotal.toFixed(2);

        document.getElementById('taxAmount').textContent =
            '₱' + tax.toFixed(2);

        document.getElementById('totalAmount').textContent =
            '₱' + total.toFixed(2);

        calcChange();
    }

    function calcChange() {

        var total =
            parseFloat(
                document.getElementById('totalAmount')
                    .textContent
                    .replace('₱', '')
            ) || 0;

        var paid =
            parseFloat(
                document.getElementById('amountPaid').value
            ) || 0;

        var change = paid - total;

        var changeEl =
            document.getElementById('changeAmount');

        if (change < 0) {

            changeEl.value = '';

            changeEl.placeholder = 'Insufficient';

        } else {

            changeEl.value = change.toFixed(2);
        }
    }

    function setPayment(method, el) {
        paymentMethod = method;

        // remove active class from all buttons
        var buttons = document.querySelectorAll('.pay-method');

        for (var i = 0; i < buttons.length; i++) {
            buttons[i].classList.remove('active');
        }

        // set active on clicked button
        el.classList.add('active');

        // show/hide cash input
        var cashGroup = document.getElementById('cashInputGroup');

        if (method === 'cash') {
            cashGroup.style.display = 'block';
        } else {
            cashGroup.style.display = 'none';
        }
    }

    var categoryTabs = document.querySelectorAll('.cat-tab');
    var products = document.querySelectorAll('.product-card');

    for (var i = 0; i < categoryTabs.length; i++) {
        categoryTabs[i].addEventListener('click', function () {

            var selected = this.dataset.cat;

            // active class toggle
            for (var j = 0; j < categoryTabs.length; j++) {
                categoryTabs[j].classList.remove('active');
            }

            this.classList.add('active');

            // filter products
            for (var k = 0; k < products.length; k++) {

                var cat = products[k].dataset.cat;

                if (selected === 'all' || cat === selected) {
                    products[k].style.display = 'block';
                } else {
                    products[k].style.display = 'none';
                }
            }
        });
    }

    renderCart();
</script>