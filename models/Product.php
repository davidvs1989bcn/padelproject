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
