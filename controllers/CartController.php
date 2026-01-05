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
            if ((int)($it['id'] ?? 0) === $productId && (string)($it['size'] ?? '') === '') {
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

        // ✅ MODO AUTOMÁTICO: si hay filas en product_sizes -> requiere talla
        $hasSizes = $productModel->hasSizes($id);

        $size = trim((string)($_POST['size'] ?? ''));

        // ====== VALIDACIÓN DE TALLA (automática por BD) ======
        if ($hasSizes) {
            if ($size === '') {
                $_SESSION['flash_error'] = "Selecciona una talla.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }

            // Validar que esa talla exista realmente en BD (si no, sizeStock devuelve 0)
            $stockSize = (int)$productModel->sizeStock($id, $size);
            if ($stockSize <= 0) {
                $_SESSION['flash_error'] = "La talla seleccionada no está disponible.";
                header("Location: " . BASE_URL . "/product/" . $id);
                exit;
            }
        } else {
            // Producto sin tallas -> forzamos size vacío
            $size = '';
        }

        // ====== CONTROL DE STOCK ======
        if ($hasSizes) {
            // Stock por talla
            $stockSize = (int)$productModel->sizeStock($id, $size);
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

        // Clave única por producto+talla (si no hay talla, quedará "id|")
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

        // 1) Aplicamos cantidades (sin validar aún) + eliminamos las de 0
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

        // 2) Validación de stock (automática por BD)
        $productModel = new Product();
        $errors = [];

        // Acumulamos por producto sin talla y por producto+talla
        $sumByProduct = [];      // productId => totalQty (solo líneas sin talla)
        $sumByProdSize = [];     // "id|size" => totalQty

        foreach ($cart as $it) {
            $pid  = (int)($it['id'] ?? 0);
            $size = (string)($it['size'] ?? '');
            $qty  = (int)($it['quantity'] ?? 0);

            if ($pid <= 0 || $qty <= 0) continue;

            if ($size !== '') {
                $k = $pid . '|' . $size;
                $sumByProdSize[$k] = ($sumByProdSize[$k] ?? 0) + $qty;
            } else {
                $sumByProduct[$pid] = ($sumByProduct[$pid] ?? 0) + $qty;
            }
        }

        // 2A) Validar líneas con talla
        foreach ($sumByProdSize as $k => $wantedQty) {
            [$pidStr, $size] = explode('|', $k, 2);
            $pid = (int)$pidStr;

            // Si el producto ya no tiene tallas, esta línea no tiene sentido
            if (!$productModel->hasSizes($pid)) {
                $errors[] = "Un producto del carrito requiere actualizarse (tallas). Se ha eliminado una línea.";
                if (isset($cart[$k])) unset($cart[$k]);
                continue;
            }

            $stockSize = (int)$productModel->sizeStock($pid, $size);

            // Si la talla ya no existe (stockSize=0), fuera
            if ($stockSize <= 0) {
                $errors[] = "La talla $size ya no está disponible. Se ha eliminado del carrito.";
                if (isset($cart[$k])) unset($cart[$k]);
                continue;
            }

            if ($wantedQty > $stockSize) {
                $errors[] = "Stock insuficiente para talla $size (producto #$pid). Máximo: $stockSize.";

                // Solo debería existir una línea por pid|size (key única), ajustamos directo
                if (isset($cart[$k])) {
                    $cart[$k]['quantity'] = $stockSize; // como stockSize>0 aquí
                }
            }
        }

        // 2B) Validar líneas sin talla
        foreach ($sumByProduct as $pid => $wantedQty) {
            // Si el producto AHORA tiene tallas, no podemos validar sin talla -> lo quitamos
            if ($productModel->hasSizes((int)$pid)) {
                $errors[] = "Un producto del carrito necesita seleccionar talla. Se ha eliminado esa línea.";
                foreach ($cart as $key => $it) {
                    if ((int)($it['id'] ?? 0) === (int)$pid && (string)($it['size'] ?? '') === '') {
                        unset($cart[$key]);
                    }
                }
                continue;
            }

            $p = $productModel->find((int)$pid);
            $stock = (int)($p['stock'] ?? 0);

            if ($wantedQty > $stock) {
                $errors[] = "Stock insuficiente para " . ($p['name'] ?? "producto #$pid") . ". Máximo: $stock.";

                // Ajuste simple: reducimos líneas sin talla de ese producto hasta stock
                $remaining = $stock;
                foreach ($cart as $key => $it) {
                    if ((int)($it['id'] ?? 0) !== (int)$pid) continue;
                    if ((string)($it['size'] ?? '') !== '') continue;

                    $q = (int)($it['quantity'] ?? 0);
                    if ($q <= 0) { unset($cart[$key]); continue; }

                    if ($remaining <= 0) {
                        unset($cart[$key]);
                        continue;
                    }

                    if ($q > $remaining) {
                        $cart[$key]['quantity'] = $remaining;
                        $remaining = 0;
                    } else {
                        $remaining -= $q;
                    }
                }
            }
        }

        if (!empty($errors)) {
            $_SESSION['flash_error'] = implode(" ", $errors);
        }

        $this->saveCart($cart);
        header("Location: " . BASE_URL . "/cart");
        exit;
    }
}
