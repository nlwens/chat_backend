<?php
namespace App\Models;

use PDO;

class Group {
    private PDO $pdo;

    public function __construct(PDO $pdo) {
        $this->pdo = $pdo;
    }

    public function create($groupName) {
        $stmt = $this->pdo->prepare('INSERT INTO groups (group_name) VALUES (:group_name)');
        $stmt->execute(['group_name' => $groupName]);
        return $this->pdo->lastInsertId();
    }

    public function getAll() {
        $stmt = $this->pdo->query('SELECT * FROM groups');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}