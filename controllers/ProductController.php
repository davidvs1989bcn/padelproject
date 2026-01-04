<?php
class ProductController {
    public function index(): void {
        $productModel = new Product();
        $products = $productModel->all();
        require 'views/products/index.php';
    }

    public function show(int $id): void {
        $productModel = new Product();
        $product = $productModel->find($id);

        if (!$product) {
            http_response_code(404);
            require 'views/layout/header.php';
            echo "<div class='container py-5 text-center'><h1>Producto no encontrado</h1>";
            echo "<a class='btn btn-primary' href='".BASE_URL."/home'>Volver</a></div>";
            require 'views/layout/footer.php';
            return;
        }

        require 'views/products/show.php';
    }
}
