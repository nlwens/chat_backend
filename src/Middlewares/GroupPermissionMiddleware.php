<?php
namespace App\Middlewares;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Models\User;
use Slim\Routing\RouteContext;

class GroupPermissionMiddleware {
    private User $userModel;

    public function __construct(User $userModel) {
        $this->userModel = $userModel;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response {
        // 从请求中获取 user_id 和 group_id
        $data = $request->getParsedBody();
        $userId = $data['userId'] ?? null;
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $groupId = $route->getArgument('groupId');


        // 检查用户是否在群组中
        if (!$this->userModel->isUserInGroup($userId, $groupId)) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'User is not in the group']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        // 如果用户在群组中，继续处理请求
        return $handler->handle($request);
    }
}