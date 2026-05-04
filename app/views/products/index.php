<div class="page-toolbar">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="url" value="products">
        <div class="input-icon">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search products...">
        </div>
        <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i></button>
    </form>
    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
    <a href="<?= BASE_URL ?>/index.php?url=products/create" class="btn btn-primary"><i class="fas fa-plus"></i> Add Product</a>
    <?php endif; ?>
</div>

<div class="card">
    <div class="card-header">
        <h3>Products (<?= count($products) ?>)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr>
                    <th>SKU</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Cost</th>
                    <th>Price</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                    <th>Actions</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                <tr><td colspan="8" class="text-center text-muted">No products found.</td></tr>
                <?php else: ?>
                <?php foreach ($products as $product): ?>
                <tr>
                    <td><code><?= htmlspecialchars($product['sku']) ?></code></td>
                    <td><strong><?= htmlspecialchars($product['name']) ?></strong></td>
                    <td><?= htmlspecialchars($product['category_name'] ?? '—') ?></td>
                    <td>₱<?= number_format($product['cost'], 2) ?></td>
                    <td>₱<?= number_format($product['price'], 2) ?></td>
                    <td>
                        <span class="stock-pill <?= $product['stock'] <= 0 ? 'out' : ($product['stock'] <= $product['low_stock_alert'] ? 'low' : 'ok') ?>">
                            <?= $product['stock'] ?>
                        </span>
                    </td>
                    <td><span class="badge badge-<?= $product['is_active'] ? 'completed' : 'voided' ?>"><?= $product['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?url=products/edit/<?= $product['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                        <a href="<?= BASE_URL ?>/index.php?url=products/delete/<?= $product['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Deactivate this product?')"><i class="fas fa-ban"></i></a>
                    </td>
                    <?php endif; ?>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
