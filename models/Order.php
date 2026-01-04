<?php

class Order {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(int $userId, array $cart, float $total): int {
        $this->conn->beginTransaction();

        $stmt = $this->conn->prepare(
            "INSERT INTO orders (user_id, total, status, created_at)
             VALUES (?, ?, 'paid', NOW())"
        );
        $stmt->execute([$userId, $total]);
        $orderId = (int)$this->conn->lastInsertId();

        // ✅ IMPORTANTE: añadimos size al INSERT
        $itemStmt = $this->conn->prepare(
            "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal, size)
             VALUES (?, ?, ?, ?, ?, ?, ?)"
        );

        foreach ($cart as $item) {
            $subtotal = ((float)$item['price']) * ((int)$item['quantity']);

            $itemStmt->execute([
                $orderId,
                (int)$item['id'],
                (string)$item['name'],
                (float)$item['price'],
                (int)$item['quantity'],
                (float)$subtotal,
                (string)($item['size'] ?? '')   // ✅ guardamos talla (o vacío)
            ]);
        }

        $this->conn->commit();
        return $orderId;
    }

    /**
     * Listado de pedidos con info extra (preview)
     */
    public function allByUser(int $userId): array {
        $sql = "
            SELECT
                o.*,
                (
                    SELECT COALESCE(SUM(oi.quantity), 0)
                    FROM order_items oi
                    WHERE oi.order_id = o.id
                ) AS item_count,
                (
                    SELECT oi2.product_name
                    FROM order_items oi2
                    WHERE oi2.order_id = o.id
                    ORDER BY oi2.id ASC
                    LIMIT 1
                ) AS first_product_name,
                (
                    SELECT p.image
                    FROM order_items oi3
                    LEFT JOIN products p ON p.id = oi3.product_id
                    WHERE oi3.order_id = o.id
                    ORDER BY oi3.id ASC
                    LIMIT 1
                ) AS first_image
            FROM orders o
            WHERE o.user_id = ?
            ORDER BY o.id DESC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$userId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findForUser(int $orderId, int $userId): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$orderId, $userId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? $row : null;
    }

    /**
     * Items del pedido con info extra del producto si existe
     */
    public function items(int $orderId): array {
        $sql = "
            SELECT
                oi.*,
                p.image AS product_image,
                p.brand AS product_brand,
                p.category AS product_category
            FROM order_items oi
            LEFT JOIN products p ON p.id = oi.product_id
            WHERE oi.order_id = ?
            ORDER BY oi.id ASC
        ";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$orderId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
