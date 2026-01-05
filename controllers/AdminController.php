<?php
class AdminController {

    private function redirect(string $path): void {
        header("Location: " . BASE_URL . $path);
        exit;
    }

    private function requireAdmin(): void {
        $user = $_SESSION['user'] ?? null;
        if (!$user || ($user['role'] ?? '') !== 'admin') {
            $this->redirect('/login');
        }
    }

    public function dashboard(): void {
        $this->requireAdmin();
        require 'views/admin/dashboard.php';
    }

    public function products(): void {
        $this->requireAdmin();
        $productModel = new Product();
        $products = $productModel->all();
        require 'views/admin/products.php';
    }

    public function create(): void {
        $this->requireAdmin();

        $brandModel = new Brand();
        $brands = $brandModel->all();

        $error = null;
        $data = [
            'name' => '',
            'brand_id' => '',
            'brand' => '',
            'category' => '',
            'price' => '',
            'stock' => '',
            'image' => '',
            'short_description' => '',
            'description' => ''
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data['name'] = trim($_POST['name'] ?? '');
            $data['brand_id'] = trim($_POST['brand_id'] ?? '');
            $data['category'] = trim($_POST['category'] ?? '');
            $data['price'] = trim($_POST['price'] ?? '');
            $data['stock'] = trim($_POST['stock'] ?? '');
            $data['image'] = trim($_POST['image'] ?? '');
            $data['short_description'] = trim($_POST['short_description'] ?? '');
            $data['description'] = trim($_POST['description'] ?? '');

            $brandId = ($data['brand_id'] === '' ? null : (int)$data['brand_id']);
            if ($brandId !== null && $brandId <= 0) $brandId = null;

            // Si hay brand_id, saco el nombre para guardarlo también en products.brand (legacy)
            $brandName = '';
            if ($brandId !== null) {
                $br = $brandModel->find($brandId);
                $brandName = $br ? (string)$br['name'] : '';
            }
            $data['brand'] = $brandName;

            if ($data['name'] === '' || $data['price'] === '' || $data['image'] === '') {
                $error = "Nombre, precio e imagen son obligatorios.";
            } elseif (!is_numeric($data['price']) || (float)$data['price'] < 0) {
                $error = "El precio debe ser un número válido.";
            } elseif ($data['stock'] !== '' && (!ctype_digit($data['stock']) || (int)$data['stock'] < 0)) {
                $error = "El stock debe ser un número entero (0 o más).";
            } else {
                $productModel = new Product();
                $ok = $productModel->create([
                    'name' => $data['name'],
                    'brand' => $data['brand'],       // legacy texto
                    'brand_id' => $brandId,          // NUEVO
                    'category' => $data['category'],
                    'price' => (float)$data['price'],
                    'stock' => ($data['stock'] === '' ? 0 : (int)$data['stock']),
                    'image' => $data['image'],
                    'short_description' => $data['short_description'],
                    'description' => $data['description']
                ]);

                if ($ok) {
                    $this->redirect('/admin/products');
                } else {
                    $error = "No se pudo crear el producto.";
                }
            }
        }

        require 'views/admin/create.php';
    }

    public function edit(int $id): void {
        $this->requireAdmin();

        $brandModel = new Brand();
        $brands = $brandModel->all();

        $productModel = new Product();
        $product = $productModel->find($id);

        if (!$product) {
            http_response_code(404);
            require 'views/layout/header.php';
            echo "<div class='container py-5 text-center'><h1>Producto no encontrado</h1>";
            echo "<a class='btn btn-primary' href='".BASE_URL."/admin/products'>Volver</a></div>";
            require 'views/layout/footer.php';
            return;
        }

        $error = null;

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $brandIdRaw = trim($_POST['brand_id'] ?? '');
            $category = trim($_POST['category'] ?? '');
            $price = trim($_POST['price'] ?? '');
            $stock = trim($_POST['stock'] ?? '');
            $image = trim($_POST['image'] ?? '');
            $short = trim($_POST['short_description'] ?? '');
            $desc = trim($_POST['description'] ?? '');

            $brandId = ($brandIdRaw === '' ? null : (int)$brandIdRaw);
            if ($brandId !== null && $brandId <= 0) $brandId = null;

            $brandName = '';
            if ($brandId !== null) {
                $br = $brandModel->find($brandId);
                $brandName = $br ? (string)$br['name'] : '';
            }

            if ($name === '' || $price === '' || $image === '') {
                $error = "Nombre, precio e imagen son obligatorios.";
            } elseif (!is_numeric($price) || (float)$price < 0) {
                $error = "El precio debe ser un número válido.";
            } elseif ($stock !== '' && (!ctype_digit($stock) || (int)$stock < 0)) {
                $error = "El stock debe ser un número entero (0 o más).";
            } else {
                $ok = $productModel->update($id, [
                    'name' => $name,
                    'brand' => $brandName,     // legacy texto
                    'brand_id' => $brandId,    // NUEVO
                    'category' => $category,
                    'price' => (float)$price,
                    'stock' => ($stock === '' ? 0 : (int)$stock),
                    'image' => $image,
                    'short_description' => $short,
                    'description' => $desc
                ]);

                if ($ok) {
                    $this->redirect('/admin/products');
                } else {
                    $error = "No se pudo actualizar el producto.";
                }
            }

            $product = $productModel->find($id);
        }

        require 'views/admin/edit.php';
    }

    public function delete(): void {
        $this->requireAdmin();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/products');
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            $this->redirect('/admin/products');
        }

        $productModel = new Product();
        $productModel->delete($id);

        $this->redirect('/admin/products');
    }
}
