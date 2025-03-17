<?php
namespace App\Models;

use PDO;

class User {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($username, $token) {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, token) VALUES (:username, :token)');
        $stmt->execute([
            'username' => $username,
            'token' => $token
        ]);
        return $this->pdo->lastInsertId();
    }
}