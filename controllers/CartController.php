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

    // Cantidad en carrito para un producto SIN tallas (sumatorio de todas sus líneas)
    private function cartQtyForProduct(array $cart, int $productId): int {
        $sum = 0;
        foreach ($cart as $it) {
            if ((int)($it['id'] ?? 0) === $productId) {
                $sum += (int)($it['quantity'] ?? 0);
            }
        }
        return $sum;
    }

    // Cantidad en carrito para un producto CON talla concreta
    private function cartQtyForProductSize(array $cart, int $productId, string $size): int {
        $sum = 0;
        foreach ($cart as $it) {
            if ((int)($it['id'] ?? 0) === $productId && (string)($it['size'] ?? '') === $size) {
                $sum += (int)($it['quantity'] ?? 0);
            }
        }
        return $sum;
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

        $cart = $this->getCart();

        // ====== VALIDACIÓN DE TALLA ======
        $category = mb_strtolower(trim($p['category'] ?? ''), 'UTF-8');
        $isClothing = ($category === 'ropa');
        $isShoes = ($category === 'zapatillas');

        // Calcetines -> talla de zapatillas
        $nameLower = mb_strtolower(trim($p['name'] ?? ''), 'UTF-8');
        $isSocks = (strpos($nameLower, 'calcet') !== false);

        $size = trim($_POST['size'] ?? '');

        // Validar talla si aplica
        if ($isShoes || $isSocks) {
            $allowed = ['37.5','38','39','40','41','42','43','44','45','46'];
            if ($size === '' || !in_array($size, $allowed, true)) {
                $_SESSION['flash_error'] = "Selecciona una talla válida.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } elseif ($isClothing) {
            $allowed = ['S','M','L','XL','XXL'];
            if ($size === '' || !in_array($size, $allowed, true)) {
                $_SESSION['flash_error'] = "Selecciona una talla válida.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } else {
            $size = '';
        }

        // ====== CONTROL DE STOCK ======
        $hasSizes = ($isClothing || $isShoes || $isSocks);

        if ($hasSizes) {
            // Stock por talla
            $stockSize = $productModel->sizeStock($id, $size);
            $inCartSize = $this->cartQtyForProductSize($cart, $id, $size);
            $available = $stockSize - $inCartSize;

            if ($available <= 0) {
                $_SESSION['flash_error'] = "Lo sentimos, no queda stock para la talla $size.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } else {
            // Stock general
            $stock = (int)($p['stock'] ?? 0);
            $inCart = $this->cartQtyForProduct($cart, $id);
            $available = $stock - $inCart;

            if ($available <= 0) {
                $_SESSION['flash_error'] = "Lo sentimos, este producto está agotado.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        }

        // Clave única por producto+talla
        $key = $id . '|' . $size;

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
