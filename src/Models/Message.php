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
        $stmt = $this->pdo->prepare('
            INSERT INTO messages (group_id, user_id, content) 
            VALUES (:group_id, :user_id, :content)'
        );
        $stmt->execute([
            'group_id' => $groupId,
            'user_id' => $userId,
            'content' => $content
        ]);
        return 'Message sent successfully';
    }

    public function getByGroup($groupId, $since): array
    {
        $stmt = $this->pdo->prepare('
            SELECT messages.*, COALESCE(users.username, \'deleted user\') AS username
            FROM messages
            LEFT JOIN users ON messages.user_id = users.id
            WHERE messages.group_id = :group_id
                --to support periodically refreshed
                AND (:since IS NULL OR messages.created_at > :since)
'       );
        $stmt->execute([
            'group_id' => $groupId,
            'since' => $since
        ]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}