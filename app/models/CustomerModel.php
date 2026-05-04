<?php
require_once __DIR__ . '/Model.php';

class CustomerModel extends Model {
    protected string $table = 'customers';

    public function create(array $data): int|false {
        $name = $this->escape($data['name']);
        $email = $this->escape($data['email'] ?? '');
        $phone = $this->escape($data['phone'] ?? '');
        $address = $this->escape($data['address'] ?? '');

        $this->db->query("INSERT INTO customers (name, email, phone, address) VALUES ('$name', '$email', '$phone', '$address')");
        return $this->db->lastInsertId() ?: false;
    }

    public function update(int $id, array $data): bool {
        $id = (int)$id;
        $name = $this->escape($data['name']);
        $email = $this->escape($data['email'] ?? '');
        $phone = $this->escape($data['phone'] ?? '');
        $address = $this->escape($data['address'] ?? '');

        $this->db->query("UPDATE customers SET name='$name', email='$email', phone='$phone', address='$address' WHERE id=$id");
        return $this->db->affectedRows() >= 0;
    }

    public function updateTotalPurchases(int $id, float $amount): void {
        $id = (int)$id;
        $amount = (float)$amount;
        $this->db->query("UPDATE customers SET total_purchases = total_purchases + $amount WHERE id=$id");
    }

    public function search(string $term): array {
        $term = $this->escape($term);
        $result = $this->db->query("SELECT * FROM customers WHERE name LIKE '%$term%' OR phone LIKE '%$term%' ORDER BY name LIMIT 20");
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}
