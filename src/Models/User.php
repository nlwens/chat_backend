<?php

namespace App\Models;

use PDO;

class User
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($username, $token): false|string
    {
        $stmt = $this->pdo->prepare('INSERT INTO users (username, token) VALUES (:username, :token)');
        $stmt->execute([
            'username' => $username,
            'token' => $token
        ]);
        return $this->pdo->lastInsertId();
    }

    // this method is only for testing use, no routes access
    public function getAllUsers():array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users');
        $stmt->execute();
        return $stmt->fetchAll();
    }
}