<?php

class Review {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    /* =====================================================
       ¿Puede el usuario reseñar este producto?
       (lo ha comprado y no lo ha reseñado aún)
    ===================================================== */
    public function canUserReviewProduct(int $productId, int $userId): bool {
        $sql = "
            SELECT COUNT(*) 
            FROM order_items oi
            INNER JOIN orders o ON o.id = oi.order_id
            WHERE oi.product_id = ?
              AND o.user_id = ?
              AND NOT EXISTS (
                  SELECT 1
                  FROM product_reviews pr
                  WHERE pr.product_id = oi.product_id
                    AND pr.user_id = o.user_id
              )
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId, $userId]);
        return (int)$stmt->fetchColumn() > 0;
    }

    /* =====================================================
       Último pedido del usuario con ese producto
    ===================================================== */
    public function latestOrderForProduct(int $productId, int $userId): ?int {
        $sql = "
            SELECT o.id
            FROM orders o
            INNER JOIN order_items oi ON oi.order_id = o.id
            WHERE oi.product_id = ?
              AND o.user_id = ?
            ORDER BY o.created_at DESC
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId, $userId]);
        $id = $stmt->fetchColumn();
        return $id ? (int)$id : null;
    }

    /* =====================================================
       Crear reseña
    ===================================================== */
    public function create(
        int $productId,
        int $userId,
        int $orderId,
        int $rating,
        ?string $title,
        ?string $comment
    ): bool {
        $rating = max(1, min(5, $rating));
        $title = trim((string)$title) ?: null;
        $comment = trim((string)$comment) ?: null;

        $stmt = $this->conn->prepare("
            INSERT INTO product_reviews
              (product_id, user_id, order_id, rating, title, body, created_at)
            VALUES (?, ?, ?, ?, ?, ?, NOW())
        ");

        return $stmt->execute([
            $productId,
            $userId,
            $orderId,
            $rating,
            $title,
            $comment
        ]);
    }

    /* =====================================================
       Reseñas de un producto
    ===================================================== */
    public function productReviews(int $productId): array {
        $sql = "
            SELECT pr.*, COALESCE(u.name, 'Cliente') AS user_name
            FROM product_reviews pr
            LEFT JOIN users u ON u.id = pr.user_id
            WHERE pr.product_id = ?
            ORDER BY pr.created_at DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$productId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /* =====================================================
       Resumen de reseñas
    ===================================================== */
    public function productSummary(int $productId): array {
        $stmt = $this->conn->prepare("
            SELECT COUNT(*) total_reviews, COALESCE(AVG(rating),0) avg_rating
            FROM product_reviews
            WHERE product_id = ?
        ");
        $stmt->execute([$productId]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total_reviews' => 0,
            'avg_rating' => 0
        ];
    }

    /* =====================================================
       Reseñas ya hechas en un pedido
    ===================================================== */
    public function reviewedOrders(int $orderId, int $userId): array {
        $stmt = $this->conn->prepare("
            SELECT product_id
            FROM product_reviews
            WHERE order_id = ? AND user_id = ?
        ");
        $stmt->execute([$orderId, $userId]);
        return array_column($stmt->fetchAll(PDO::FETCH_ASSOC), 'product_id');
    }
}
