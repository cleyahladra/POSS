<?php
require_once __DIR__ . '/Model.php';

class CategoryModel extends Model {
    protected string $table = 'categories';

    public function create(array $data): int|false {
        $name = $this->escape($data['name']);
        $desc = $this->escape($data['description'] ?? '');
        $this->db->query("INSERT INTO categories (name, description) VALUES ('$name', '$desc')");
        return $this->db->lastInsertId() ?: false;
    }

    public function update(int $id, array $data): bool {
        $id = (int)$id;
        $name = $this->escape($data['name']);
        $desc = $this->escape($data['description'] ?? '');
        $this->db->query("UPDATE categories SET name='$name', description='$desc' WHERE id=$id");
        return $this->db->affectedRows() >= 0;
    }
}
