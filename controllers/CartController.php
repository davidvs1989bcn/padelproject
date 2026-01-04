<?php
class CartController {

    private function getCart(): array {
        if (!isset($_SESSION['cart']) || !is_array($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
        return $_SESSION['cart'];
    }

    private function saveCart(array $cart): void {
        $_SESSION['cart'] = $cart;
    }

    public function index(): void {
        $cart = $this->getCart();

        $total = 0;
        foreach ($cart as $it) {
            $total += (float)$it['price'] * (int)$it['quantity'];
        }

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

        $category = mb_strtolower(trim($p['category'] ?? ''), 'UTF-8');
        $isClothing = ($category === 'ropa');
        $isShoes = ($category === 'zapatillas');

        // ✅ Calcetines -> talla de zapatillas (37.5 a 46)
        $nameLower = mb_strtolower(trim($p['name'] ?? ''), 'UTF-8');
        $isSocks = (strpos($nameLower, 'calcet') !== false);

        $size = trim($_POST['size'] ?? '');

        // Validar talla si corresponde
        if ($isShoes || $isSocks) {
            $allowed = ['37.5','38','39','40','41','42','43','44','45','46'];
            if ($size === '' || !in_array($size, $allowed, true)) {
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } elseif ($isClothing) {
            $allowed = ['S','M','L','XL','XXL'];
            if ($size === '' || !in_array($size, $allowed, true)) {
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } else {
            // No talla en palas/bolsas/pelotas
            $size = '';
        }

        $cart = $this->getCart();

        // Clave única por producto+talla (ej: "12|XL")
        $key = $id . '|' . $size;

        // ==========================
        // ✅ CONTROL STOCK POR TALLA
        // ==========================
        // Solo aplicamos control si este producto+talla existe en product_sizes
        // (si no existe fila, lo tratamos como "sin control por talla")
        if ($size !== '' && $productModel->hasSizeStockRow($id, $size)) {
            $currentQtyInCart = isset($cart[$key]) ? (int)$cart[$key]['quantity'] : 0;
            $desiredQty = $currentQtyInCart + 1;

            $stock = $productModel->getSizeStock($id, $size); // aquí ya no será null porque hasSizeStockRow true
            $stock = (int)$stock;

            if ($desiredQty > $stock) {
                header("Location: " . BASE_URL . "/product/" . $id . "?err=stock");
                exit;
            }
        }

        if (!isset($cart[$key])) {
            $cart[$key] = [
                'key' => $key,
                'id' => (int)$p['id'],
                'name' => (string)$p['name'],
                'price' => (float)$p['price'],
                'image' => (string)$p['image'],
                'quantity' => 1,
                'size' => $size,
                'category' => (string)($p['category'] ?? '')
            ];
        } else {
            $cart[$key]['quantity'] += 1;
        }

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }

    public function remove(): void {
        $key = trim($_POST['key'] ?? '');
        $cart = $this->getCart();

        if ($key !== '' && isset($cart[$key])) {
            unset($cart[$key]);
        }

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

        // (opcional) aquí también podríamos validar que no se suba a más del stock,
        // pero lo importante es bloquear en checkout (lo haremos en Order::create)

        foreach ($_POST['qty'] as $key => $qty) {
            $key = (string)$key;
            $qty = (int)$qty;

            if (!isset($cart[$key])) continue;

            if ($qty <= 0) {
                unset($cart[$key]);
            } else {
                $cart[$key]['quantity'] = $qty;
            }
        }

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }
}
