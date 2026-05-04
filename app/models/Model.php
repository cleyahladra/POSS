<?php
class Model {
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function findAll(string $conditions = '', string $orderBy = '', int $limit = 0): array {
        $sql = "SELECT * FROM {$this->table}";
        if ($conditions) $sql .= " WHERE $conditions";
        if ($orderBy) $sql .= " ORDER BY $orderBy";
        if ($limit) $sql .= " LIMIT $limit";

        $result = $this->db->query($sql);
        if (!$result) return [];

        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function findById(int $id): array|null {
        $id = (int)$id;
        $result = $this->db->query("SELECT * FROM {$this->table} WHERE {$this->primaryKey} = $id LIMIT 1");
        if (!$result) return null;
        return $result->fetch_assoc() ?: null;
    }

    public function count(string $conditions = ''): int {
        $sql = "SELECT COUNT(*) as total FROM {$this->table}";
        if ($conditions) $sql .= " WHERE $conditions";
        $result = $this->db->query($sql);
        if (!$result) return 0;
        $row = $result->fetch_assoc();
        return (int)($row['total'] ?? 0);
    }

    public function delete(int $id): bool {
        $id = (int)$id;
        $this->db->query("DELETE FROM {$this->table} WHERE {$this->primaryKey} = $id");
        return $this->db->affectedRows() > 0;
    }

    protected function escape(string $value): string {
        return $this->db->escape($value);
    }
}
