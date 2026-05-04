<?php
require_once __DIR__ . '/Model.php';

class ProductModel extends Model {
    protected string $table = 'products';

    public function findAllWithCategory(string $search = ''): array {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id";
        if ($search) {
            $search = $this->escape($search);
            $sql .= " WHERE p.name LIKE '%$search%' OR p.sku LIKE '%$search%'";
        }
        $sql .= " ORDER BY p.name ASC";
        $result = $this->db->query($sql);
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public function findActive(string $search = ''): array {
        $sql = "SELECT p.*, c.name as category_name 
                FROM products p 
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1";
        if ($search) {
            $search = $this->escape($search);
            $sql .= " AND (p.name LIKE '%$search%' OR p.sku LIKE '%$search%')";
        }
        $sql .= " ORDER BY p.name ASC";
        $result = $this->db->query($sql);
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public function findBySku(string $sku): array|null {
        $sku = $this->escape($sku);
        $result = $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.sku = '$sku' AND p.is_active = 1 LIMIT 1");
        if (!$result) return null;
        return $result->fetch_assoc() ?: null;
    }

    public function create(array $data): int|false {
        $name = $this->escape($data['name']);
        $sku = $this->escape($data['sku']);
        $desc = $this->escape($data['description'] ?? '');
        $price = (float)$data['price'];
        $cost = (float)$data['cost'];
        $stock = (int)$data['stock'];
        $low = (int)($data['low_stock_alert'] ?? 10);
        $cat = $data['category_id'] ? (int)$data['category_id'] : 'NULL';

        $sql = "INSERT INTO products (category_id, name, sku, description, price, cost, stock, low_stock_alert) 
                VALUES ($cat, '$name', '$sku', '$desc', $price, $cost, $stock, $low)";
        $this->db->query($sql);
        return $this->db->lastInsertId() ?: false;
    }

    public function update(int $id, array $data): bool {
        $id = (int)$id;
        $name = $this->escape($data['name']);
        $sku = $this->escape($data['sku']);
        $desc = $this->escape($data['description'] ?? '');
        $price = (float)$data['price'];
        $cost = (float)$data['cost'];
        $stock = (int)$data['stock'];
        $low = (int)($data['low_stock_alert'] ?? 10);
        $cat = $data['category_id'] ? (int)$data['category_id'] : 'NULL';
        $active = (int)($data['is_active'] ?? 1);

        $sql = "UPDATE products SET category_id=$cat, name='$name', sku='$sku', description='$desc', 
                price=$price, cost=$cost, stock=$stock, low_stock_alert=$low, is_active=$active WHERE id=$id";
        $this->db->query($sql);
        return $this->db->affectedRows() >= 0;
    }

    public function updateStock(int $id, int $qty): bool {
        $id = (int)$id;
        $qty = (int)$qty;
        $this->db->query("UPDATE products SET stock = stock - $qty WHERE id = $id");
        return $this->db->affectedRows() > 0;
    }

    public function getLowStock(): array {
        $result = $this->db->query("SELECT p.*, c.name as category_name FROM products p LEFT JOIN categories c ON p.category_id = c.id WHERE p.stock <= p.low_stock_alert AND p.is_active = 1 ORDER BY p.stock ASC");
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
