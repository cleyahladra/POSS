<?php
require_once __DIR__ . '/Model.php';

class SaleModel extends Model {
    protected string $table = 'sales';

    public function generateInvoiceNumber(): string {
        $prefix = 'INV-' . date('Ymd') . '-';
        $result = $this->db->query("SELECT COUNT(*) as cnt FROM sales WHERE invoice_number LIKE '$prefix%'");
        $row = $result->fetch_assoc();
        $num = str_pad(($row['cnt'] + 1), 4, '0', STR_PAD_LEFT);
        return $prefix . $num;
    }

    public function createSale(array $sale, array $items): int|false {
        $this->db->beginTransaction();
        try {
            $invoice = $this->escape($this->generateInvoiceNumber());
            $customerId = $sale['customer_id'] ? (int)$sale['customer_id'] : 'NULL';
            $userId = (int)$sale['user_id'];
            $subtotal = (float)$sale['subtotal'];
            $taxRate = (float)$sale['tax_rate'];
            $taxAmount = (float)$sale['tax_amount'];
            $discount = (float)$sale['discount_amount'];
            $total = (float)$sale['total_amount'];
            $paid = (float)$sale['amount_paid'];
            $change = (float)$sale['change_amount'];
            $payment = $this->escape($sale['payment_method']);
            $notes = $this->escape($sale['notes'] ?? '');

            $sql = "INSERT INTO sales (invoice_number, customer_id, user_id, subtotal, tax_rate, tax_amount, 
                    discount_amount, total_amount, amount_paid, change_amount, payment_method, notes)
                    VALUES ('$invoice', $customerId, $userId, $subtotal, $taxRate, $taxAmount, 
                    $discount, $total, $paid, $change, '$payment', '$notes')";
            $this->db->query($sql);
            $saleId = $this->db->lastInsertId();

            if (!$saleId) throw new Exception('Failed to create sale');

            foreach ($items as $item) {
                $productId = (int)$item['product_id'];
                $productName = $this->escape($item['product_name']);
                $qty = (int)$item['quantity'];
                $unitPrice = (float)$item['unit_price'];
                $itemDiscount = (float)($item['discount'] ?? 0);
                $itemSubtotal = (float)$item['subtotal'];

                $this->db->query("INSERT INTO sale_items (sale_id, product_id, product_name, quantity, unit_price, discount, subtotal)
                    VALUES ($saleId, $productId, '$productName', $qty, $unitPrice, $itemDiscount, $itemSubtotal)");

                $this->db->query("UPDATE products SET stock = stock - $qty WHERE id = $productId");
            }

            if ($sale['customer_id']) {
                $cid = (int)$sale['customer_id'];
                $this->db->query("UPDATE customers SET total_purchases = total_purchases + $total WHERE id=$cid");
            }

            $this->db->commit();
            return $saleId;
        } catch (Exception $e) {
            $this->db->rollback();
            error_log('Sale creation failed: ' . $e->getMessage());
            return false;
        }
    }

    public function findAllWithDetails(array $filters = []): array {
        $sql = "SELECT s.*, u.name as cashier_name, c.name as customer_name
                FROM sales s
                LEFT JOIN users u ON s.user_id = u.id
                LEFT JOIN customers c ON s.customer_id = c.id
                WHERE 1=1";

        if (!empty($filters['date_from'])) {
            $df = $this->escape($filters['date_from']);
            $sql .= " AND DATE(s.created_at) >= '$df'";
        }
        if (!empty($filters['date_to'])) {
            $dt = $this->escape($filters['date_to']);
            $sql .= " AND DATE(s.created_at) <= '$dt'";
        }
        if (!empty($filters['status'])) {
            $st = $this->escape($filters['status']);
            $sql .= " AND s.status = '$st'";
        }

        $sql .= " ORDER BY s.created_at DESC";

        if (!empty($filters['limit'])) {
            $sql .= " LIMIT " . (int)$filters['limit'];
        }

        $result = $this->db->query($sql);
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public function findByIdWithItems(int $id): array|null {
        $id = (int)$id;
        $result = $this->db->query("SELECT s.*, u.name as cashier_name, c.name as customer_name
            FROM sales s
            LEFT JOIN users u ON s.user_id = u.id
            LEFT JOIN customers c ON s.customer_id = c.id
            WHERE s.id = $id LIMIT 1");
        if (!$result) return null;
        $sale = $result->fetch_assoc();
        if (!$sale) return null;

        $itemsResult = $this->db->query("SELECT * FROM sale_items WHERE sale_id = $id");
        $sale['items'] = [];
        while ($item = $itemsResult->fetch_assoc()) {
            $sale['items'][] = $item;
        }
        return $sale;
    }

    public function voidSale(int $id): bool {
        $id = (int)$id;
        $items = $this->db->query("SELECT * FROM sale_items WHERE sale_id = $id");
        while ($item = $items->fetch_assoc()) {
            $pid = (int)$item['product_id'];
            $qty = (int)$item['quantity'];
            $this->db->query("UPDATE products SET stock = stock + $qty WHERE id = $pid");
        }
        $this->db->query("UPDATE sales SET status='voided' WHERE id=$id");
        return $this->db->affectedRows() > 0;
    }

    public function getDailySummary(string $date = ''): array {
        if (!$date) $date = date('Y-m-d');
        $date = $this->escape($date);
        $result = $this->db->query("SELECT 
            COUNT(*) as total_transactions,
            SUM(total_amount) as total_sales,
            SUM(tax_amount) as total_tax,
            SUM(discount_amount) as total_discount,
            AVG(total_amount) as avg_transaction
            FROM sales 
            WHERE DATE(created_at) = '$date' AND status = 'completed'");
        return $result->fetch_assoc() ?? [];
    }

    public function getMonthlySales(int $year = 0): array {
        if (!$year) $year = (int)date('Y');
        // Fix: Added MONTHNAME to GROUP BY clause
        $result = $this->db->query("SELECT 
            MONTH(created_at) as month,
            MONTHNAME(created_at) as month_name,
            COUNT(*) as transactions,
            SUM(total_amount) as total
            FROM sales
            WHERE YEAR(created_at) = $year AND status = 'completed'
            GROUP BY MONTH(created_at), MONTHNAME(created_at)
            ORDER BY MONTH(created_at)");
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }

    public function getTopProducts(int $limit = 10): array {
        $limit = (int)$limit;
        // Fix: Added si.product_id and si.product_name to GROUP BY clause
        $result = $this->db->query("SELECT si.product_name, SUM(si.quantity) as total_qty, SUM(si.subtotal) as total_revenue
            FROM sale_items si
            JOIN sales s ON si.sale_id = s.id
            WHERE s.status = 'completed'
            GROUP BY si.product_id, si.product_name
            ORDER BY total_revenue DESC
            LIMIT $limit");
        if (!$result) return [];
        $rows = [];
        while ($row = $result->fetch_assoc()) $rows[] = $row;
        return $rows;
    }
}