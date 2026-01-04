<?php
class Order {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(int $userId, array $cart, float $total): int {
        $this->conn->beginTransaction();

        try {
            // 1) Insert pedido
            $stmt = $this->conn->prepare(
                "INSERT INTO orders (user_id, total, status, created_at)
                 VALUES (?, ?, 'paid', NOW())"
            );
            $stmt->execute([$userId, $total]);
            $orderId = (int)$this->conn->lastInsertId();

            // 2) Descontar stock por talla (si aplica)
            $productModel = new Product();

            foreach ($cart as $item) {
                $pid = (int)($item['id'] ?? 0);
                $qty = (int)($item['quantity'] ?? 0);
                $size = trim((string)($item['size'] ?? ''));

                if ($pid <= 0 || $qty <= 0) continue;

                // solo si tiene talla y existe fila en product_sizes
                if ($size !== '' && $productModel->hasSizeStockRow($pid, $size)) {
                    $ok = $productModel->decreaseSizeStock($pid, $size, $qty);
                    if (!$ok) {
                        // No hay stock suficiente
                        $this->conn->rollBack();
                        throw new Exception("No hay stock suficiente para el producto #{$pid} talla {$size}.");
                    }
                }
            }

            // 3) Insert items
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
                    (string)($item['size'] ?? '')
                ]);
            }

            $this->conn->commit();
            return $orderId;

        } catch (Throwable $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            // Re-lanzamos para que el controller lo muestre bonito
            throw $e;
        }
    }

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
                ) AS first_image,
                (
                    SELECT CASE WHEN COUNT(*) > 0 THEN 1 ELSE 0 END
                    FROM order_items oi4
                    WHERE oi4.order_id = o.id
                      AND oi4.size IS NOT NULL
                      AND TRIM(oi4.size) <> ''
                ) AS has_sizes
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
