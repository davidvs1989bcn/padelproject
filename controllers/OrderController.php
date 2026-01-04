<?php
class OrderController {

    private function requireLogin(): void {
        if (!isset($_SESSION['user'])) {
            header("Location: " . BASE_URL . "/login");
            exit;
        }
    }

    private function getCart(): array {
        return $_SESSION['cart'] ?? [];
    }

    public function index(): void {
        $this->requireLogin();

        $orderModel = new Order();
        $orders = $orderModel->allByUser((int)$_SESSION['user']['id']);

        require 'views/orders/index.php';
    }

    public function checkout(): void {
        $this->requireLogin();

        $cart = $this->getCart();
        if (empty($cart)) {
            header("Location: " . BASE_URL . "/cart");
            exit;
        }

        $total = 0;
        foreach ($cart as $it) $total += $it['price'] * $it['quantity'];

        $orderModel = new Order();
        $orderId = $orderModel->create((int)$_SESSION['user']['id'], $cart, (float)$total);

        // Vaciar carrito
        $_SESSION['cart'] = [];

        header("Location: " . BASE_URL . "/order/" . $orderId);
        exit;
    }

    public function show(int $orderId): void {
        $this->requireLogin();

        $orderModel = new Order();
        $order = $orderModel->findForUser($orderId, (int)$_SESSION['user']['id']);

        if (!$order) {
            http_response_code(404);
            require 'views/layout/header.php';
            echo "<div class='container py-5 text-center'><h1>Pedido no encontrado</h1>";
            echo "<a class='btn btn-primary' href='".BASE_URL."/orders'>Volver</a></div>";
            require 'views/layout/footer.php';
            return;
        }

        $items = $orderModel->items($orderId);
        require 'views/orders/show.php';
    }
}
