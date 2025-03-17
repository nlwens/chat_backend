<?php
namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Random\RandomException;

class UserController {
    private User $userModel;

    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    // create new user

    /**
     * @throws RandomException
     */
    public function create(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $userName = $data['username'];
        $token = bin2hex(random_bytes(16));
        $userId = $this->userModel->create($userName, $token);

        $response->getBody()->write(json_encode([
            'id' => $userId,
            'username' => $userName,
            'token' => $token
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    //user join a group
    public function joinGroup(Request $request, Response $response) {
        $data = $request->getParsedBody();
        $userId = $data['userId'] ?? null;
        $queryParams = $request->getQueryParams();
        $groupId = $queryParams['groupId'] ?? null;

        try {
            // 调用 addUserToGroup 方法
            if ($this->userModel->addUserToGroup($userId, $groupId)) {
                $response->getBody()->write(json_encode(['message' => 'User joined the group successfully']));
                return $response->withHeader('Content-Type', 'application/json');
            }
        } catch (\Exception $e) {
            // 捕获异常并返回错误信息
            $response->getBody()->write(json_encode(['message' => $e->getMessage()]));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['message' => 'Unknown error occurred']));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}