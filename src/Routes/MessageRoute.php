<?php

use App\Middlewares\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\MessageController;
use App\Models\Message;
use App\Middlewares\GroupPermissionMiddleware;
use App\Models\User;

return function (RouteCollectorProxy $message, $pdo) {
    $messageModel = new Message($pdo);
    $userModel = new User($pdo);
    $messageController = new MessageController($messageModel);

    // 发送消息（需要群组权限检查）
    $message->post('/groups/{groupId}/messages', [$messageController, 'sendMessage'])
        ->add(new GroupPermissionMiddleware($userModel))
        ->add(new AuthMiddleware($pdo));

    // 获取群组消息（需要群组权限检查）
    $message->get('/groups/{groupId}/messages', [$messageController, 'getMessagesByGroup'])
        ->add(new GroupPermissionMiddleware($userModel))
        ->add(new AuthMiddleware($pdo));
};