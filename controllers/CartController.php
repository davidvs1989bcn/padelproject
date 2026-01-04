<?php
class CartController {

    private function getCart(): array {
        if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
        return $_SESSION['cart'];
    }

    private function saveCart(array $cart): void {
        $_SESSION['cart'] = $cart;
    }

    public function index(): void {
        $cart = $this->getCart();

        $total = 0;
        foreach ($cart as $it) $total += $it['price'] * $it['quantity'];

        require 'views/cart/index.php';
    }

    public function add(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        if ($id <= 0) {
            header("Location: " . BASE_URL . "/home");
            exit;
        }

        $productModel = new Product();
        $p = $productModel->find($id);
        if (!$p) {
            header("Location: " . BASE_URL . "/home");
            exit;
        }

        $cart = $this->getCart();

        if (!isset($cart[$id])) {
            $cart[$id] = [
                'id' => (int)$p['id'],
                'name' => $p['name'],
                'price' => (float)$p['price'],
                'image' => $p['image'],
                'quantity' => 1
            ];
        } else {
            $cart[$id]['quantity'] += 1;
        }

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }

    public function remove(): void {
        $id = isset($_POST['id']) ? (int)$_POST['id'] : 0;
        $cart = $this->getCart();
        if (isset($cart[$id])) unset($cart[$id]);

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }

    public function update(): void {
        $cart = $this->getCart();
        if (!isset($_POST['qty']) || !is_array($_POST['qty'])) {
            header("Location: " . BASE_URL . "/cart");
            exit;
        }

        foreach ($_POST['qty'] as $id => $qty) {
            $id = (int)$id;
            $qty = (int)$qty;

            if (!isset($cart[$id])) continue;

            if ($qty <= 0) {
                unset($cart[$id]);
            } else {
                $cart[$id]['quantity'] = $qty;
            }
        }

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }
}
