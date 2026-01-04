<?php
class Order {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(int $userId, array $cart, float $total): int {
        $this->conn->beginTransaction();

        $stmt = $this->conn->prepare("INSERT INTO orders (user_id, total, status, created_at) VALUES (?, ?, 'paid', NOW())");
        $stmt->execute([$userId, $total]);
        $orderId = (int)$this->conn->lastInsertId();

        $itemStmt = $this->conn->prepare("
            INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal)
            VALUES (?, ?, ?, ?, ?, ?)
        ");

        foreach ($cart as $item) {
            $subtotal = $item['price'] * $item['quantity'];
            $itemStmt->execute([
                $orderId,
                $item['id'],
                $item['name'],
                $item['price'],
                $item['quantity'],
                $subtotal
            ]);
        }

        $this->conn->commit();
        return $orderId;
    }

    public function allByUser(int $userId): array {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY id DESC");
        $stmt->execute([$userId]);
        return $stmt->fetchAll();
    }

    public function findForUser(int $orderId, int $userId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function items(int $orderId): array {
        $stmt = $this->conn->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id ASC");
        $stmt->execute([$orderId]);
        return $stmt->fetchAll();
    }
}
