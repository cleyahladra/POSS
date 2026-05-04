<div class="page-toolbar">
    <form method="GET" action="" class="filter-form">
        <input type="hidden" name="url" value="customers">
        <div class="input-icon">
            <i class="fas fa-search"></i>
            <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Search by name or phone...">
        </div>
        <button type="submit" class="btn btn-outline"><i class="fas fa-search"></i></button>
    </form>
    <a href="<?= BASE_URL ?>/index.php?url=customers/create" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add Customer</a>
</div>

<div class="card">
    <div class="card-header">
        <h3>Customers (<?= count($customers) ?>)</h3>
    </div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Phone</th><th>Total Purchases</th><th>Member Since</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                <tr><td colspan="6" class="text-center text-muted">No customers found.</td></tr>
                <?php else: ?>
                <?php foreach ($customers as $c): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($c['name']) ?></strong></td>
                    <td><?= htmlspecialchars($c['email'] ?? '—') ?></td>
                    <td><?= htmlspecialchars($c['phone'] ?? '—') ?></td>
                    <td><strong>₱<?= number_format($c['total_purchases'], 2) ?></strong></td>
                    <td><?= date('M d, Y', strtotime($c['created_at'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?url=customers/edit/<?= $c['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                        <?php if (($_SESSION['user']['role'] ?? '') === 'admin'): ?>
                        <a href="<?= BASE_URL ?>/index.php?url=customers/delete/<?= $c['id'] ?>" class="btn btn-sm btn-danger"
                           onclick="return confirm('Delete this customer?')"><i class="fas fa-trash"></i></a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
