<?php

use App\Middlewares\AuthMiddleware;
use App\Middlewares\GroupPermissionMiddleware;
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\GroupController;
use App\Models\Group;

return function (RouteCollectorProxy $group, $pdo) {
    $groupModel = new Group($pdo);
    $groupController = new GroupController($groupModel);

    $group->group('', function (RouteCollectorProxy $group) use ($groupController, $groupModel) {

        // get all groups
        $group->get('/users/{userId}', [$groupController, 'getAll']);

        // create a new group
        $group->post('/users/{userId}', [$groupController, 'create']);

        // user join or leave a group
        $group->post('/{groupId}/users/{userId}/members', [$groupController, 'joinGroup']);

        // user leave a group
        $group->delete('/{groupId}/users/{userId}/members', [$groupController, 'leaveGroup'])
            ->add(new GroupPermissionMiddleware($groupModel));

    })->add(new AuthMiddleware($pdo));
};