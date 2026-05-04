<div class="page-toolbar">
    <a href="<?= BASE_URL ?>/index.php?url=users/create" class="btn btn-primary"><i class="fas fa-user-plus"></i> Add User</a>
</div>

<div class="card">
    <div class="card-header"><h3>System Users</h3></div>
    <div class="card-body p-0">
        <table class="table">
            <thead>
                <tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Created</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($users as $u): ?>
                <tr>
                    <td><strong><?= htmlspecialchars($u['name']) ?></strong></td>
                    <td><?= htmlspecialchars($u['email']) ?></td>
                    <td><span class="badge badge-<?= $u['role'] === 'admin' ? 'cash' : 'gcash' ?>"><?= ucfirst($u['role']) ?></span></td>
                    <td><span class="badge badge-<?= $u['is_active'] ? 'completed' : 'voided' ?>"><?= $u['is_active'] ? 'Active' : 'Inactive' ?></span></td>
                    <td><?= date('M d, Y', strtotime($u['created_at'])) ?></td>
                    <td>
                        <a href="<?= BASE_URL ?>/index.php?url=users/edit/<?= $u['id'] ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
