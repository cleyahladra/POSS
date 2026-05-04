<div class="form-page">
    <div class="page-actions">
        <a href="<?= BASE_URL ?>/index.php?url=users" class="btn btn-outline"><i class="fas fa-arrow-left"></i> Back</a>
    </div>
    <div class="card" style="max-width:500px">
        <div class="card-header"><h3><?= $user ? 'Edit User' : 'Add User' ?></h3></div>
        <div class="card-body">
            <form method="POST" action="">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" name="name" value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                </div>
                <div class="form-group">
                    <label>Password <?= $user ? '(leave blank to keep)' : '*' ?></label>
                    <input type="password" name="password" <?= !$user ? 'required' : '' ?> autocomplete="new-password">
                </div>
                <div class="form-group">
                    <label>Role *</label>
                    <select name="role">
                        <option value="cashier" <?= ($user['role'] ?? '') === 'cashier' ? 'selected' : '' ?>>Cashier</option>
                        <option value="admin" <?= ($user['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                    </select>
                </div>
                <?php if ($user): ?>
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" <?= ($user['is_active'] ?? 1) ? 'checked' : '' ?>> Active
                    </label>
                </div>
                <?php endif; ?>
                <div class="form-actions">
                    <a href="<?= BASE_URL ?>/index.php?url=users" class="btn btn-outline">Cancel</a>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>
