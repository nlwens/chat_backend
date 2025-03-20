<?php

use App\Middlewares\AuthMiddleware;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\MessageController;
use App\Models\Message;
use App\Middlewares\GroupPermissionMiddleware;
use App\Models\Group;

return function (RouteCollectorProxy $message, $pdo) {
    $messageModel = new Message($pdo);
    $groupModel = new Group($pdo);
    $messageController = new MessageController($messageModel);

    $message->group('', function (RouteCollectorProxy $message) use ($messageController) {

        // Send a message to a group
        $message->post('/groups/{groupId}/users/{userId}/messages', [$messageController, 'sendMessage']);

        // Get all message from a group
        $message->get('/groups/{groupId}/users/{userId}/messages', [$messageController, 'getMessagesByGroup']);

    })->add(new GroupPermissionMiddleware($groupModel))
        ->add(new AuthMiddleware($pdo));
};