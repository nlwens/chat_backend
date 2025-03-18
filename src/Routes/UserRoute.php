<?php

use App\Controllers\MessageController;
use App\Models\Message;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\UserController;
use App\Models\User;
use App\Middlewares\GroupPermissionMiddleware;

return function (RouteCollectorProxy $user, $pdo) {
    $userModel = new User($pdo);
    $userController = new UserController($userModel);

    // create new user
    $user->post('', [$userController, 'create']);

    // user join a group
    $user->post('/join', [$userController, 'joinGroup']);
};