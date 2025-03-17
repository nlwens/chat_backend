<?php
namespace App\Models;

use Exception;
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

    /**
     * @throws Exception
     */
    public function addUserToGroup($userId, $groupId) {
        // 使用准备好的语句来查询数据库
        if (!$this->ifGroupExists($groupId)) {
            throw new Exception('Group does not exist'); // 抛出异常，群组不存在
        }

        // 检查用户是否已经在群组中
        if ($this->isUserInGroup($userId, $groupId)) {
            throw new Exception('User already in the group'); // 抛出异常，用户已经在群组中
        }

        // 将用户加入群组
        try {
            $stmt = $this->pdo->prepare('INSERT INTO user_groups (user_id, group_id) VALUES (:user_id, :group_id)');
            $stmt->execute([
                'user_id' => $userId,
                'group_id' => $groupId
            ]);
            return true; // 如果成功加入群组，返回 true
        } catch (\PDOException $e) {
            throw new Exception('Failed to add user to the group: ' . $e->getMessage()); // 抛出数据库错误
        }
    }

    public function ifGroupExists($groupId) {
        $stmt = $this->pdo->prepare('SELECT * FROM groups WHERE id = :groupId');
        $stmt->execute(['groupId' => $groupId]);
        return $stmt->fetch() !== false;
    }

    public function isUserInGroup($userId, $groupId) {
        $stmt = $this->pdo->prepare('SELECT * FROM user_groups WHERE user_id = :user_id AND group_id = :group_id');
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
        return $stmt->fetch() !== false;
    }
}