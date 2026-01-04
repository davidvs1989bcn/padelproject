<?php
class User {
    private PDO $conn;

    public function __construct() {
        $db = new Database();
        $this->conn = $db->getConnection();
    }

    public function findByEmail(string $email): ?array {
        $stmt = $this->conn->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    // ✅ Registro con pregunta + respuesta
    public function create(string $name, string $email, string $password, string $securityQuestion, string $securityAnswer): bool {
        $hash = password_hash($password, PASSWORD_BCRYPT);

        $normalizedAnswer = mb_strtolower(trim($securityAnswer), 'UTF-8');
        $answerHash = password_hash($normalizedAnswer, PASSWORD_BCRYPT);

        $stmt = $this->conn->prepare(
            "INSERT INTO users (name, email, password, role, security_question, security_answer_hash)
             VALUES (?, ?, ?, 'user', ?, ?)"
        );

        return $stmt->execute([$name, $email, $hash, $securityQuestion, $answerHash]);
    }

    public function verifyLogin(string $email, string $password): ?array {
        $user = $this->findByEmail($email);
        if (!$user) return null;
        if (!password_verify($password, $user['password'])) return null;
        return $user;
    }

    public function getSecurityQuestionByEmail(string $email): ?string {
        $stmt = $this->conn->prepare("SELECT security_question FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row) return null;
        return $row['security_question'] ?? null;
    }

    public function verifySecurityAnswer(string $email, string $answer): bool {
        $stmt = $this->conn->prepare("SELECT security_answer_hash FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$row || empty($row['security_answer_hash'])) return false;

        $normalized = mb_strtolower(trim($answer), 'UTF-8');
        return password_verify($normalized, $row['security_answer_hash']);
    }

    public function updatePasswordByEmail(string $email, string $newPassword): bool {
        $hash = password_hash($newPassword, PASSWORD_BCRYPT);
        $stmt = $this->conn->prepare("UPDATE users SET password = ? WHERE email = ?");
        return $stmt->execute([$hash, $email]);
    }
}
