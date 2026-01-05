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

        $minPrice = trim($_GET['min_price'] ?? '');
        $maxPrice = trim($_GET['max_price'] ?? '');
        $sort = trim($_GET['sort'] ?? '');

        // brands[] ahora son IDs
        $brands = $_GET['brands'] ?? [];
        if (!is_array($brands)) $brands = [];
        $brands = array_values(array_filter(array_map('intval', $brands), fn($v) => $v > 0));

        // Usamos SIEMPRE el filter (ya cubre todo: q/section/precio/marca/sort)
        $products = $productModel->filterProducts([
            'q' => $q,
            'section' => $section,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'brands' => $brands,
            'sort' => $sort
        ]);

        if ($q !== '' && $section !== '') {
            $title = "Resultados en " . $this->sectionLabel($section) . " para: " . $q;
        } elseif ($q !== '') {
            $title = "Resultados para: " . $q;
        } elseif ($section !== '') {
            $title = "Sección: " . $this->sectionLabel($section);
        } else {
            $title = "Catálogo Completo";
        }

        // marcas disponibles para el sidebar
        $brandsList = $productModel->brandsList([
            'q' => $q,
            'section' => $section,
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);

        // valores para la vista
        $selectedBrands = $brands;

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
