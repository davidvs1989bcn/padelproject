<?php
class Product {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function all(): array {
        $stmt = $this->conn->query("SELECT * FROM products ORDER BY id DESC");
        return $stmt->fetchAll();
    }

    public function find(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function searchByName(string $q): array {
        $qLike = '%' . $q . '%';
        $stmt = $this->conn->prepare("SELECT * FROM products WHERE name LIKE ? ORDER BY id DESC");
        $stmt->execute([$qLike]);
        return $stmt->fetchAll();
    }

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
        return $stmt->fetchAll();
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
        return $stmt->fetchAll();
    }

    // ADMIN CRUD
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
