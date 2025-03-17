<?php
namespace App\Controllers;

use App\Models\Group;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GroupController {
    private Group $groupModel;

    public function __construct(Group $groupModel) {
        $this->groupModel = $groupModel;
    }

    public function getAll(Request $request, Response $response) {
        $groups = $this->groupModel->getAll();
        $response->getBody()->write(json_encode($groups));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 创建群组
    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $groupName = $data['group_name'];

        $groupId = $this->groupModel->create($groupName);
        $response->getBody()->write(json_encode(['id' => $groupId]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    //user join a group
    public function joinGroup(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $data['user_id'];
        $groupId = $args['groupId'];

        // 将用户加入群组
        if ($this->groupModel->addUserToGroup($userId, $groupId)) {
            $response->getBody()->write(json_encode(['message' => 'User joined the group successfully']));
            return $response->withHeader('Content-Type', 'application/json');
        } else {
            $response->getBody()->write(json_encode(['message' => 'User is already in the group']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }
    }
}