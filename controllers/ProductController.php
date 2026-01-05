<?php

class ProductController {

    private function sectionLabel(string $section): string {
        $s = mb_strtolower(trim($section), 'UTF-8');
        return match($s) {
            'palas' => 'Palas',
            'zapatillas' => 'Zapatillas',
            'ropa' => 'Ropa',
            'bolsas' => 'Bolsas',
            default => $section
        };
    }

    public function index(): void {
        $productModel = new Product();

        $q = trim($_GET['q'] ?? '');
        $section = trim($_GET['section'] ?? '');

        if ($q !== '' && $section !== '') {
            $products = $productModel->searchInSection($q, $section);
            $title = "Resultados en " . $this->sectionLabel($section) . " para: " . $q;
        } elseif ($q !== '') {
            $products = $productModel->searchByName($q);
            $title = "Resultados para: " . $q;
        } elseif ($section !== '') {
            $products = $productModel->allBySection($section);
            $title = "Sección: " . $this->sectionLabel($section);
        } else {
            $products = $productModel->all();
            $title = "Catálogo Completo";
        }

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

        // Detectar si usa tallas para cargar stock por talla
        $category = mb_strtolower(trim($product['category'] ?? ''), 'UTF-8');
        $isClothing = ($category === 'ropa');
        $isShoes = ($category === 'zapatillas');

        $nameLower = mb_strtolower(trim($product['name'] ?? ''), 'UTF-8');
        $isSocks = (strpos($nameLower, 'calcet') !== false);

        $hasSizes = ($isClothing || $isShoes || $isSocks);

        $sizeStocks = [];
        if ($hasSizes) {
            $sizeStocks = $productModel->sizeStocks((int)$product['id']);
        }

        require 'views/products/show.php';
    }
}
