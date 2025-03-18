<?php

use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

// 创建 Slim 应用
$app = AppFactory::create();
$app->addBodyParsingMiddleware();

// 加载数据库配置
$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['path']);

// 加载 Group 路由
$groupRoutes = require __DIR__ . '/../src/Routes/GroupRoute.php';
$userRoutes = require __DIR__ . '/../src/Routes/UserRoute.php';
$messageRoutes = require __DIR__ . '/../src/Routes/MessageRoute.php';

// 注册 Group 路由
$app->group('/groups', function ($group) use ($groupRoutes, $pdo) {
    $groupRoutes($group, $pdo);
});

$app->group('/users', function ($user) use ($userRoutes, $pdo) {
    $userRoutes($user, $pdo);
});

$app->group('', function ($message) use ($messageRoutes, $pdo) {
    $messageRoutes($message, $pdo);
});

ini_set('display_errors', 1);  // 显示所有错误
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);  // 显示所有级别的错误

// 运行应用
$app->run();