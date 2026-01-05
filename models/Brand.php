<?php

class Brand {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function all(): array {
        $stmt = $this->conn->query("SELECT id, name FROM brands ORDER BY name ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function find(int $id): ?array {
        $stmt = $this->conn->prepare("SELECT id, name FROM brands WHERE id = ? LIMIT 1");
        $stmt->execute([$id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }
}
