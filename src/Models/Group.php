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

    public function isUserInGroup($userId, $groupId) {
        $stmt = $this->pdo->prepare('SELECT * FROM user_groups WHERE user_id = :user_id AND group_id = :group_id');
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
        return $stmt->fetch() !== false;
    }

    public function addUserToGroup($userId, $groupId) {
        // 检查用户是否已经在群组中
        if ($this->isUserInGroup($userId, $groupId)) {
            return false; // 用户已经在群组中
        }

        // 将用户加入群组
        $stmt = $this->pdo->prepare('INSERT INTO user_groups (user_id, group_id) VALUES (:user_id, :group_id)');
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
        return true;
    }
}