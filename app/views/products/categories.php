<div class="two-col-layout">
    <!-- Categories List -->
    <div class="card">
        <div class="card-header">
            <h3>Categories (<?= count($categories) ?>)</h3>
        </div>
        <div class="card-body p-0">
            <table class="table">
                <thead>
                    <tr><th>Name</th><th>Description</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    <?php if (empty($categories)): ?>
                    <tr><td colspan="3" class="text-center text-muted">No categories yet.</td></tr>
                    <?php else: ?>
                    <?php foreach ($categories as $cat): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($cat['name']) ?></strong></td>
                        <td><?= htmlspecialchars($cat['description'] ?? '—') ?></td>
                        <td>
                            <button class="btn btn-sm btn-outline" onclick="editCategory(<?= $cat['id'] ?>, '<?= htmlspecialchars(addslashes($cat['name'])) ?>', '<?= htmlspecialchars(addslashes($cat['description'] ?? '')) ?>')">
                                <i class="fas fa-edit"></i>
                            </button>
                            <a href="<?= BASE_URL ?>/index.php?url=products/deleteCategory/<?= $cat['id'] ?>" class="btn btn-sm btn-danger"
                               onclick="return confirm('Delete this category?')"><i class="fas fa-trash"></i></a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Add/Edit Form -->
    <div class="card">
        <div class="card-header">
            <h3 id="formTitle">Add Category</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="<?= BASE_URL ?>/index.php?url=products/saveCategory">
                <input type="hidden" name="id" id="catId" value="0">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" name="name" id="catName" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" id="catDesc" rows="3"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="resetForm()">Reset</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, desc) {
    document.getElementById('catId').value = id;
    document.getElementById('catName').value = name;
    document.getElementById('catDesc').value = desc;
    document.getElementById('formTitle').textContent = 'Edit Category';
}
function resetForm() {
    document.getElementById('catId').value = 0;
    document.getElementById('catName').value = '';
    document.getElementById('catDesc').value = '';
    document.getElementById('formTitle').textContent = 'Add Category';
}
</script>
