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

        $brands = $_GET['brands'] ?? [];
        if (!is_array($brands)) $brands = [];
        $brands = array_values(array_filter(array_map('intval', $brands), fn($v) => $v > 0));

        $products = $productModel->filterProducts([
            'q' => $q,
            'section' => $section,
            'min_price' => $minPrice,
            'max_price' => $maxPrice,
            'brands' => $brands,
            'sort' => $sort
        ]);

        foreach ($products as &$p) {
            $p['has_sizes'] = $productModel->hasSizes((int)$p['id']);
        }
        unset($p);

        if ($q !== '' && $section !== '') {
            $title = "Resultados en " . $this->sectionLabel($section) . " para: " . $q;
        } elseif ($q !== '') {
            $title = "Resultados para: " . $q;
        } elseif ($section !== '') {
            $title = "Sección: " . $this->sectionLabel($section);
        } else {
            $title = "Catálogo Completo";
        }

        $brandsList = $productModel->brandsList([
            'q' => $q,
            'section' => $section,
            'min_price' => $minPrice,
            'max_price' => $maxPrice
        ]);

        $selectedBrands = $brands;

        require 'views/products/index.php';
    }

    public function show(int $id): void {
        $productModel = new Product();
        $reviewModel = new Review();

        $product = $productModel->find($id);
        if (!$product) {
            http_response_code(404);
            require 'views/layout/header.php';
            echo "<div class='container py-5 text-center'>
                    <h1>Producto no encontrado</h1>
                    <a class='btn btn-primary' href='".BASE_URL."/products'>Volver</a>
                  </div>";
            require 'views/layout/footer.php';
            return;
        }

        // ===== TALLAS =====
        $hasSizes = $productModel->hasSizes((int)$product['id']);
        $sizeStocks = [];
        if ($hasSizes) {
            $sizeStocks = $productModel->sizeStocks((int)$product['id']);
        }

        // ===== RESEÑAS =====
        $reviewStats = $reviewModel->productSummary((int)$product['id']);
        $reviews = $reviewModel->productReviews((int)$product['id']);

        $canReview = false;
        if (isset($_SESSION['user'])) {
            $canReview = $reviewModel->canUserReviewProduct(
                (int)$product['id'],
                (int)$_SESSION['user']['id']
            );
        }

        require 'views/products/show.php';
    }
}
