<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\UserController;
use App\Models\User;

return function (RouteCollectorProxy $user, $pdo) {
    $userModel = new User($pdo);
    $userController = new UserController($userModel);

    // create new user
    $user->post('', [$userController, 'create']);

    // user join a group
    $user->post('/join', [$userController, 'joinGroup']);
};