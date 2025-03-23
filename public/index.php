<?php
use Slim\Factory\AppFactory;
require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$app->addBodyParsingMiddleware();

$config = require __DIR__ . '/../config/database.php';
$pdo = new PDO('sqlite:' . $config['path']);

$groupRoutes = require __DIR__ . '/../src/Routes/GroupRoute.php';
$userRoutes = require __DIR__ . '/../src/Routes/UserRoute.php';
$messageRoutes = require __DIR__ . '/../src/Routes/MessageRoute.php';

$app->group('/groups', function ($group) use ($groupRoutes, $pdo) {
    $groupRoutes($group, $pdo);
});

$app->group('/users', function ($user) use ($userRoutes, $pdo) {
    $userRoutes($user, $pdo);
});

$app->group('', function ($message) use ($messageRoutes, $pdo) {
    $messageRoutes($message, $pdo);
});

$app->run();