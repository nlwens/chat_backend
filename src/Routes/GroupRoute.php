<?php
use Slim\Routing\RouteCollectorProxy;
use App\Controllers\GroupController;
use App\Models\Group;

return function (RouteCollectorProxy $group, $pdo) {
    $groupModel = new Group($pdo);
    $groupController = new GroupController($groupModel);

    // 获取所有群组
    $group->get('', [$groupController, 'getAll']);

    // 创建群组
    $group->post('', [$groupController, 'create']);

    // user join a group
    $group->post('/{groupId}/join', [$groupController, 'joinGroup']);
};