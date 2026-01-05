<?php

class Order {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function create(int $userId, array $cart, float $total): int {

        try {
            $this->conn->beginTransaction();

            // 1) Crear pedido
            $stmt = $this->conn->prepare(
                "INSERT INTO orders (user_id, total, status, created_at)
                 VALUES (?, ?, 'paid', NOW())"
            );
            $stmt->execute([$userId, $total]);
            $orderId = (int)$this->conn->lastInsertId();

            // 2) Preparar INSERT de items (con size)
            $itemStmt = $this->conn->prepare(
                "INSERT INTO order_items (order_id, product_id, product_name, unit_price, quantity, subtotal, size)
                 VALUES (?, ?, ?, ?, ?, ?, ?)"
            );

            // 3) Preparar UPDATEs de stock (ATÓMICOS)
            // Stock general (sin talla)
            $decGeneralStmt = $this->conn->prepare(
                "UPDATE products
                 SET stock = stock - ?
                 WHERE id = ? AND stock >= ?"
            );

            // Stock por talla
            $decSizeStmt = $this->conn->prepare(
                "UPDATE product_sizes
                 SET stock = stock - ?
                 WHERE product_id = ? AND size = ? AND stock >= ?"
            );

            $inserted = 0;

            foreach ($cart as $item) {
                $pid  = (int)($item['id'] ?? 0);
                $qty  = (int)($item['quantity'] ?? 0);
                $size = trim((string)($item['size'] ?? ''));

                if ($pid <= 0 || $qty <= 0) {
                    continue;
                }

                // 1) Descontar stock en BD (si falla -> rollback)
                if ($size !== '') {
                    // Stock por talla
                    $decSizeStmt->execute([$qty, $pid, $size, $qty]);
                    if ($decSizeStmt->rowCount() === 0) {
                        throw new Exception("No hay stock suficiente para la talla $size.");
                    }
                } else {
                    // Stock general
                    $decGeneralStmt->execute([$qty, $pid, $qty]);
                    if ($decGeneralStmt->rowCount() === 0) {
                        throw new Exception("No hay stock suficiente para este producto.");
                    }
                }

                // 2) Guardar item
                $unitPrice = (float)($item['price'] ?? 0);
                $subtotal  = $unitPrice * $qty;

                $itemStmt->execute([
                    $orderId,
                    $pid,
                    (string)($item['name'] ?? ''),
                    $unitPrice,
                    $qty,
                    (float)$subtotal,
                    $size
                ]);

                $inserted++;
            }

            if ($inserted <= 0) {
                throw new Exception("No se pudo crear el pedido: carrito vacío o inválido.");
            }

            $this->conn->commit();
            return $orderId;

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
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
                    WHERE oi4.order_id = o.id AND TRIM(COALESCE(oi4.size,'')) <> ''
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
