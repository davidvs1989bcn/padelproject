<?php

class Product {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function all(): array {
        $stmt = $this->conn->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ========= BUSCADOR =========
    public function searchByName(string $q): array {
        $qLike = '%' . $q . '%';
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
        $stmt->execute([$qLike]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ========= SECCIONES =========
    public function allBySection(string $section): array {
        $section = mb_strtolower(trim($section), 'UTF-8');

        $where = "";
        $params = [];

        if ($section === 'ropa') {
            $where = "category = ?";
            $params = ['Ropa'];
        } elseif ($section === 'bolsas') {
            $where = "(category = ? OR category = ?)";
            $params = ['Bolsas', 'Paleteros'];
        } elseif ($section === 'zapatillas') {
            $where = "category = ?";
            $params = ['Zapatillas'];
        } elseif ($section === 'palas') {
            $where = "category = ?";
            $params = ['Palas'];
        } else {
            return $this->all();
        }

        $stmt = $this->conn->prepare("SELECT * FROM products WHERE $where ORDER BY id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function searchInSection(string $q, string $section): array {
        $section = mb_strtolower(trim($section), 'UTF-8');
        $qLike = '%' . $q . '%';

        $where = "";
        $params = [];

        if ($section === 'ropa') {
            $where = "category = ? AND name LIKE ?";
            $params = ['Ropa', $qLike];
        } elseif ($section === 'bolsas') {
            $where = "(category = ? OR category = ?) AND name LIKE ?";
            $params = ['Bolsas', 'Paleteros', $qLike];
        } elseif ($section === 'zapatillas') {
            $where = "category = ? AND name LIKE ?";
            $params = ['Zapatillas', $qLike];
        } elseif ($section === 'palas') {
            $where = "category = ? AND name LIKE ?";
            $params = ['Palas', $qLike];
        } else {
            return $this->searchByName($q);
        }

        $stmt = $this->conn->prepare("SELECT * FROM products WHERE $where ORDER BY id DESC");
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ===== STOCK POR TALLA (tabla product_sizes) =====
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
        return $map; // ['M' => 10, 'L' => 0, ...]
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

    /**
     * Descontar stock GENERAL (products.stock) de forma segura.
     * Devuelve true si descuenta, false si no había suficiente.
     */
    public function decrementGeneralStock(int $productId, int $qty): bool {
        $stmt = $this->conn->prepare(
            "UPDATE products
             SET stock = stock - ?
             WHERE id = ? AND stock >= ?"
        );
        $stmt->execute([$qty, $productId, $qty]);
        return $stmt->rowCount() > 0;
    }

    /**
     * Descontar stock POR TALLA (product_sizes.stock) de forma segura.
     * Devuelve true si descuenta, false si no había suficiente.
     */
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
        $sql = "INSERT INTO products (name, brand, category, price, stock, image, short_description, description)
                VALUES (:name, :brand, :category, :price, :stock, :image, :short_description, :description)";
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([
            ':name' => $data['name'],
            ':brand' => $data['brand'],
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
            ':brand' => $data['brand'],
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
