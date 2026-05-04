<div class="form-page">
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/index.php?url=customers" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card" style="max-width:600px">
        <div class="card-header">
            <h3><?= $customer ? 'Edit Customer' : 'Add New Customer' ?></h3>
        </div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($customer['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($customer['email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone" value="<?= htmlspecialchars($customer['phone'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Address</label>
                    <textarea name="address" rows="3"><?= htmlspecialchars($customer['address'] ?? '') ?></textarea>
                </div>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/index.php?url=customers" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> <?= $customer ? 'Update' : 'Create' ?></button>
                </div>
            </form>
        </div>
    </div>
</div>
