<?php

class Product {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function all(): array {
        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            ORDER BY p.id DESC
        ";
        $stmt = $this->conn->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE p.id = ?
            LIMIT 1
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ========= BUSCADOR =========
    public function searchByName(string $q): array {
        $qLike = '%' . $q . '%';
        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE p.name LIKE ?
            ORDER BY p.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$qLike]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========= SECCIONES =========
    public function allBySection(string $section): array {
        $section = mb_strtolower(trim($section), 'UTF-8');

        $where = "";
        $params = [];

        if ($section === 'ropa') {
            $where = "p.category = ?";
            $params = ['Ropa'];
        } elseif ($section === 'bolsas') {
            $where = "(p.category = ? OR p.category = ?)";
            $params = ['Bolsas', 'Paleteros'];
        } elseif ($section === 'zapatillas') {
            $where = "p.category = ?";
            $params = ['Zapatillas'];
        } elseif ($section === 'palas') {
            $where = "p.category = ?";
            $params = ['Palas'];
        } else {
            return $this->all();
        }

        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE $where
            ORDER BY p.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchInSection(string $q, string $section): array {
        $section = mb_strtolower(trim($section), 'UTF-8');
        $qLike = '%' . $q . '%';

        $where = "";
        $params = [];

        if ($section === 'ropa') {
            $where = "p.category = ? AND p.name LIKE ?";
            $params = ['Ropa', $qLike];
        } elseif ($section === 'bolsas') {
            $where = "(p.category = ? OR p.category = ?) AND p.name LIKE ?";
            $params = ['Bolsas', 'Paleteros', $qLike];
        } elseif ($section === 'zapatillas') {
            $where = "p.category = ? AND p.name LIKE ?";
            $params = ['Zapatillas', $qLike];
        } elseif ($section === 'palas') {
            $where = "p.category = ? AND p.name LIKE ?";
            $params = ['Palas', $qLike];
        } else {
            return $this->searchByName($q);
        }

        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
            WHERE $where
            ORDER BY p.id DESC
        ";
        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========= FILTROS =========
    public function filterProducts(array $f): array {
        $q = trim((string)($f['q'] ?? ''));
        $section = trim((string)($f['section'] ?? ''));
        $minPrice = trim((string)($f['min_price'] ?? ''));
        $maxPrice = trim((string)($f['max_price'] ?? ''));
        $brandIds = $f['brands'] ?? [];
        if (!is_array($brandIds)) $brandIds = [];
        $brandIds = array_values(array_filter(array_map('intval', $brandIds), fn($v) => $v > 0));
        $sort = trim((string)($f['sort'] ?? ''));

        $where = [];
        $params = [];

        $sec = mb_strtolower(trim($section), 'UTF-8');
        if ($sec === 'ropa') {
            $where[] = "p.category = ?";
            $params[] = 'Ropa';
        } elseif ($sec === 'bolsas') {
            $where[] = "(p.category = ? OR p.category = ?)";
            $params[] = 'Bolsas';
            $params[] = 'Paleteros';
        } elseif ($sec === 'zapatillas') {
            $where[] = "p.category = ?";
            $params[] = 'Zapatillas';
        } elseif ($sec === 'palas') {
            $where[] = "p.category = ?";
            $params[] = 'Palas';
        }

        if ($q !== '') {
            $where[] = "p.name LIKE ?";
            $params[] = '%' . $q . '%';
        }

        if ($minPrice !== '' && is_numeric($minPrice)) {
            $where[] = "p.price >= ?";
            $params[] = (float)$minPrice;
        }
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $where[] = "p.price <= ?";
            $params[] = (float)$maxPrice;
        }

        if (!empty($brandIds)) {
            $placeholders = implode(',', array_fill(0, count($brandIds), '?'));
            $where[] = "p.brand_id IN ($placeholders)";
            foreach ($brandIds as $bid) $params[] = (int)$bid;
        }

        $sql = "
            SELECT p.*, COALESCE(b.name, p.brand) AS brand_name
            FROM products p
            LEFT JOIN brands b ON b.id = p.brand_id
        ";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }

        $orderBy = " ORDER BY p.id DESC";
        if ($sort === 'price_asc') $orderBy = " ORDER BY p.price ASC, p.id DESC";
        elseif ($sort === 'price_desc') $orderBy = " ORDER BY p.price DESC, p.id DESC";
        elseif ($sort === 'name_asc') $orderBy = " ORDER BY p.name ASC, p.id DESC";
        elseif ($sort === 'newest') $orderBy = " ORDER BY p.created_at DESC, p.id DESC";

        $sql .= $orderBy;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function brandsList(array $f = []): array {
        $q = trim((string)($f['q'] ?? ''));
        $section = trim((string)($f['section'] ?? ''));
        $minPrice = trim((string)($f['min_price'] ?? ''));
        $maxPrice = trim((string)($f['max_price'] ?? ''));

        $where = [];
        $params = [];

        $sec = mb_strtolower(trim($section), 'UTF-8');
        if ($sec === 'ropa') {
            $where[] = "p.category = ?";
            $params[] = 'Ropa';
        } elseif ($sec === 'bolsas') {
            $where[] = "(p.category = ? OR p.category = ?)";
            $params[] = 'Bolsas';
            $params[] = 'Paleteros';
        } elseif ($sec === 'zapatillas') {
            $where[] = "p.category = ?";
            $params[] = 'Zapatillas';
        } elseif ($sec === 'palas') {
            $where[] = "p.category = ?";
            $params[] = 'Palas';
        }

        if ($q !== '') {
            $where[] = "p.name LIKE ?";
            $params[] = '%' . $q . '%';
        }

        if ($minPrice !== '' && is_numeric($minPrice)) {
            $where[] = "p.price >= ?";
            $params[] = (float)$minPrice;
        }
        if ($maxPrice !== '' && is_numeric($maxPrice)) {
            $where[] = "p.price <= ?";
            $params[] = (float)$maxPrice;
        }

        $sql = "
            SELECT DISTINCT b.id, b.name
            FROM products p
            INNER JOIN brands b ON b.id = p.brand_id
        ";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(" AND ", $where);
        }
        $sql .= " ORDER BY b.name ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ============================
    // ✅ NUEVO: detectar si un producto tiene tallas en BD
    // ============================
    public function hasSizes(int $productId): bool {
        $stmt = $this->conn->prepare(
            "SELECT 1 FROM product_sizes WHERE product_id = ? LIMIT 1"
        );
        $stmt->execute([$productId]);
        return (bool)$stmt->fetchColumn();
    }

    // ===== STOCK POR TALLA =====
    public function sizeStocks(int $productId): array {
        $stmt = $this->conn->prepare(
            "SELECT size, stock FROM product_sizes WHERE product_id = ?"
        );
        $stmt->execute([$productId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $map = [];
        foreach ($rows as $r) {
            $map[(string)$r['size']] = (int)$r['stock'];
        }
        return $map;
    }

    public function sizeStock(int $productId, string $size): int {
        $stmt = $this->conn->prepare(
            "SELECT stock FROM product_sizes WHERE product_id = ? AND size = ? LIMIT 1"
        );
        $stmt->execute([$productId, $size]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ? (int)$row['stock'] : 0;
    }

    public function totalSizeStock(int $productId): int {
        $stmt = $this->conn->prepare(
            "SELECT COALESCE(SUM(stock), 0) AS total_stock
             FROM product_sizes
             WHERE product_id = ?"
        );
        $stmt->execute([$productId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total_stock'] ?? 0);
    }

    public function decrementGeneralStock(int $productId, int $qty): bool {
        $stmt = $this->conn->prepare(
            "UPDATE products
             SET stock = stock - ?
             WHERE id = ? AND stock >= ?"
        );
        $stmt->execute([$qty, $productId, $qty]);
        return $stmt->rowCount() > 0;
    }

    public function decrementSizeStock(int $productId, string $size, int $qty): bool {
        $stmt = $this->conn->prepare(
            "UPDATE product_sizes
             SET stock = stock - ?
             WHERE product_id = ? AND size = ? AND stock >= ?"
        );
        $stmt->execute([$qty, $productId, $size, $qty]);
        return $stmt->rowCount() > 0;
    }

    // ========= ADMIN CRUD =========
    public function create(array $data): bool {
        $sql = "INSERT INTO products (name, brand, brand_id, category, price, stock, image, short_description, description)
                VALUES (:name, :brand, :brand_id, :category, :price, :stock, :image, :short_description, :description)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name' => $data['name'],
            ':brand' => $data['brand'] ?? '',
            ':brand_id' => ($data['brand_id'] ?? null),
            ':category' => $data['category'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':image' => $data['image'],
            ':short_description' => $data['short_description'],
            ':description' => $data['description']
        ]);
    }

    public function update(int $id, array $data): bool {
        $sql = "UPDATE products
                SET name = :name,
                    brand = :brand,
                    brand_id = :brand_id,
                    category = :category,
                    price = :price,
                    stock = :stock,
                    image = :image,
                    short_description = :short_description,
                    description = :description
                WHERE id = :id";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':id' => $id,
            ':name' => $data['name'],
            ':brand' => $data['brand'] ?? '',
            ':brand_id' => ($data['brand_id'] ?? null),
            ':category' => $data['category'],
            ':price' => $data['price'],
            ':stock' => $data['stock'],
            ':image' => $data['image'],
            ':short_description' => $data['short_description'],
            ':description' => $data['description']
        ]);
    }

    public function delete(int $id): bool {
        $stmt = $this->conn->prepare("DELETE FROM products WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
