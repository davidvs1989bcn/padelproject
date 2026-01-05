<?php

class ReviewController
{
    private function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    public function createForm(): void
    {
        $this->requireLogin();

        $productId = (int)($_GET['product_id'] ?? 0);
        if ($productId <= 0) {
            header("Location: " . BASE_URL);
            exit;
        }

        $productModel = new Product();
        $reviewModel  = new Review();

        $product = $productModel->find($productId);
        if (!$product) {
            header("Location: " . BASE_URL);
            exit;
        }

        $userId = (int)$_SESSION['user']['id'];

        if (!$reviewModel->canUserReviewProduct($productId, $userId)) {
            $_SESSION['flash_error'] = 'No puedes reseñar este producto.';
            header("Location: " . BASE_URL . "/product/$productId");
            exit;
        }

        // Si tu vista necesita $orderId (lo tienes en el create.php), lo calculamos aquí:
        $orderId = (int)($reviewModel->latestOrderForProduct($productId, $userId) ?? 0);

        require 'views/reviews/create.php';
    }

    public function create(): void
    {
        $this->requireLogin();

        $productId = (int)($_POST['product_id'] ?? 0);
        $rating    = (int)($_POST['rating'] ?? 0);
        $title     = trim((string)($_POST['title'] ?? ''));
        $comment   = trim((string)($_POST['body'] ?? '')); // ✅ antes era 'comment'

        // Si la BD tiene body NOT NULL, evitamos NULL siempre
        // (si está vacío, guardamos string vacío)
        if ($comment === '') {
            $comment = '';
        }

        // Validación base
        if ($productId <= 0 || $rating < 1 || $rating > 5) {
            $_SESSION['flash_error'] = 'Datos inválidos para publicar la reseña.';
            header("Location: " . BASE_URL . "/product/$productId");
            exit;
        }

        $reviewModel = new Review();
        $userId      = (int)$_SESSION['user']['id'];

        // Seguridad: solo permitir reseña si existe pedido válido del producto para ese usuario
        $orderId = $reviewModel->latestOrderForProduct($productId, $userId);
        if (!$orderId) {
            $_SESSION['flash_error'] = 'No puedes reseñar este producto.';
            header("Location: " . BASE_URL . "/product/$productId");
            exit;
        }

        // Si título queda vacío, lo guardamos como null para que sea realmente "opcional"
        $titleToSave = ($title !== '') ? $title : null;

        $reviewModel->create(
            $productId,
            $userId,
            (int)$orderId,
            $rating,
            $titleToSave,
            $comment
        );

        $_SESSION['flash_success'] = 'Reseña publicada correctamente.';

        // Respeta el redirect del formulario (product/order)
        $redirect = trim((string)($_POST['redirect'] ?? 'product'));
        $redirect = ($redirect === 'order') ? 'order' : 'product';

        if ($redirect === 'order') {
            header("Location: " . BASE_URL . "/orders");
        } else {
            header("Location: " . BASE_URL . "/product/$productId#reviews");
        }
        exit;
    }
}
