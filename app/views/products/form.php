<div class="form-page">
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/index.php?url=products" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>

    <div class="card">
        <div class="card-header">
            <h3><?= $product ? 'Edit Product' : 'Add New Product' ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-grid">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" value="<?= htmlspecialchars($product['name'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>SKU *</label>
                        <input type="text" name="sku" value="<?= htmlspecialchars($product['sku'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Category</label>
                        <select name="category_id">
                            <option value="">— None —</option>
                            <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= (($product['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['name']) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Cost Price *</label>
                        <input type="number" name="cost" step="0.01" min="0" value="<?= htmlspecialchars($product['cost'] ?? '0') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Selling Price *</label>
                        <input type="number" name="price" step="0.01" min="0" value="<?= htmlspecialchars($product['price'] ?? '0') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock" min="0" value="<?= htmlspecialchars($product['stock'] ?? '0') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Low Stock Alert Level</label>
                        <input type="number" name="low_stock_alert" min="0" value="<?= htmlspecialchars($product['low_stock_alert'] ?? '10') ?>">
                    </div>
                    <div class="form-group span-2">
                        <label>Description</label>
                        <textarea name="description" rows="3"><?= htmlspecialchars($product['description'] ?? '') ?></textarea>
                    </div>
                    <?php if ($product): ?>
                    <div class="form-group">
                        <label class="checkbox-label">
                            <input type="checkbox" name="is_active" <?= ($product['is_active'] ?? 1) ? 'checked' : '' ?>>
                            Active Product
                        </label>
                    </div>
                    <?php endif; ?>
                </div>

                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/index.php?url=products" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> <?= $product ? 'Update Product' : 'Create Product' ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
