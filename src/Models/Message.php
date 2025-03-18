<?php

namespace App\Models;

use PDO;

class Message
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($groupId, $userId, $content): false|string
    {
        $stmt = $this->pdo->prepare('INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)');
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
            'content' => $content
        ]);
        return $this->pdo->lastInsertId();
    }

    public function getByGroup($groupId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM messages WHERE group_id = :group_id');
        $stmt->execute(['group_id' => $groupId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}