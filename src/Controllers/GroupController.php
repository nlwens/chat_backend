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
}