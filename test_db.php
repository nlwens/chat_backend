<?php
// 连接到 SQLite 数据库
$pdo = new PDO('sqlite:database/chat.db');

// 测试连接是否成功
if ($pdo) {
    echo "Connected to the database successfully!\n";
} else {
    echo "Failed to connect to the database.\n";
    exit;
}

// 测试创建群组
$groupId = createGroup('Group 1');
echo "Created group with ID: $groupId\n";

// 测试创建用户
$user = createUser('user1');
echo "Created user with ID: {$user['id']} and token: {$user['token']}\n";

// 测试发送消息
$messageId = sendMessage($groupId, $user['id'], 'Hello, World!');
echo "Sent message with ID: $messageId\n";

// 测试获取群组消息
$messages = getMessagesByGroup($groupId);
echo "Messages in group $groupId:\n";
print_r($messages);

// 检查表是否存在
$tables = ['groups', 'users', 'messages'];
foreach ($tables as $table) {
    $stmt = $pdo->query("SELECT name FROM sqlite_master WHERE type='table' AND name='$table'");
    if ($stmt->fetch()) {
        echo "Table '$table' exists.\n";
    } else {
        echo "Table '$table' does not exist.\n";
    }
}

function createGroup($name) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO groups (group_name) VALUES (:name)');
    $stmt->execute(['name' => $name]);
    return $pdo->lastInsertId();
}

/**
 * @throws \Random\RandomException
 */
function generateToken() {
    return bin2hex(random_bytes(16)); // 生成一个 32 字符的随机字符串
}

function createUser($username) {
    global $pdo;
    $token = generateToken();
    $stmt = $pdo->prepare('INSERT INTO users (username, token) VALUES (:username, :token)');
    $stmt->execute([
        'username' => $username,
        'token' => $token
    ]);
    return [
        'id' => $pdo->lastInsertId(),
        'token' => $token
    ];
}

function sendMessage($groupId, $userId, $content) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO messages (group_id, user_id, content) VALUES (:group_id, :user_id, :content)');
    $stmt->execute([
        'group_id' => $groupId,
        'user_id' => $userId,
        'content' => $content
    ]);
    return $pdo->lastInsertId();
}

function getMessagesByGroup($groupId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT * FROM messages WHERE group_id = :group_id');
    $stmt->execute(['group_id' => $groupId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}