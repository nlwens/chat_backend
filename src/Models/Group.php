<?php

namespace App\Models;

use Exception;
use PDO;

class Group
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function create($groupName): false|string
    {
        $stmt = $this->pdo->prepare('
            INSERT INTO groups (group_name) 
            VALUES (:group_name)'
        );
        $stmt->execute(['group_name' => $groupName]);
        return $this->pdo->lastInsertId();
    }

    public function getAll(): array
    {
        $stmt = $this->pdo->query('SELECT * FROM groups');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @throws Exception
     */
    public function addUserToGroup($userId, $groupId): true
    {
        // check if this group exists
        if (!$this->ifGroupExists($groupId)) {
            throw new Exception('Cannot find the group', 404);
        }

        // check if user already in the group
        if ($this->isUserInGroup($userId, $groupId)) {
            throw new Exception('User is already in the group', 409);
        }

        try {
            $stmt = $this->pdo->prepare('
                INSERT INTO user_groups (user_id, group_id) 
                VALUES (:user_id, :group_id)'
            );
            $stmt->execute([
                'user_id' => $userId,
                'group_id' => $groupId
            ]);
            return true;
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage(), 500);
        }
    }

    /**
     * @throws Exception
     * Whether user is in the group or if the group exists
     * are already checked in middleware
     */
    public function deleteUserFromGroup($userId, $groupId): true
    {
        try {
            $stmt = $this->pdo->prepare('
                DELETE FROM user_groups 
                WHERE user_id = :user_id AND group_id = :group_id'
            );
            $stmt->execute([
                'user_id' => $userId,
                'group_id' => $groupId
            ]);
            return true;
        } catch (\PDOException $e) {
            throw new Exception($e->getMessage());
        }
    }

    public function ifGroupExists($groupId): bool
    {
        $stmt = $this->pdo->prepare('SELECT * FROM groups WHERE id = :groupId');
        $stmt->execute(['groupId' => $groupId]);
        return $stmt->fetch() !== false;
    }

    public function isUserInGroup($userId, $groupId): bool
    {
        $stmt = $this->pdo->prepare('
            SELECT * FROM user_groups 
            WHERE user_id = :user_id AND group_id = :group_id'
        );
        $stmt->execute([
            'user_id' => $userId,
            'group_id' => $groupId
        ]);
        return $stmt->fetch() !== false;
    }
}