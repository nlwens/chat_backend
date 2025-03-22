<?php

namespace Unit\Middleware;

use App\Middlewares\GroupPermissionMiddleware;
use App\Models\Group;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Request;

class GroupPermissionMiddlewareTest extends TestCase
{
    private PDO $pdo;
    private Group $groupModel;
    private GroupPermissionMiddleware $middleware;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE user_groups
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL,
                group_id   INTEGER NOT NULL,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
                FOREIGN KEY (user_id) REFERENCES users (id),
                FOREIGN KEY (group_id) REFERENCES groups (id)
            );
            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
        ");

        $this->groupModel = new Group($this->pdo);
        $this->middleware = new GroupPermissionMiddleware($this->groupModel);

        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    private function createRequest($path, $headers): Request
    {
        $request = $this->requestFactory->createRequest('GET', $path);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        return $request;
    }

    private function createHandlerMock(bool $shouldHandle): RequestHandlerInterface
    {
        $handler = $this->createMock(RequestHandlerInterface::class);
        $expects = $shouldHandle ? $this->once() : $this->never();
        $handler->expects($expects)
            ->method('handle')
            ->willReturn($this->responseFactory->createResponse(200));
        return $handler;
    }

    public function testUserInGroup()
    {
        $request = $this->createRequest('/groups/1', ['X-User-Id' => 1]);
        $handler = $this->createHandlerMock(true);

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEmpty((string)$response->getBody());
    }

    public function testUserNotInGroup()
    {
        $request = $this->createRequest('/groups/2', ['X-User-Id' => 1]);
        $handler = $this->createHandlerMock(false);

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }

    public function testMissingUserId()
    {
        $request = $this->createRequest('/groups/1', []);
        $handler = $this->createHandlerMock(false);

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }

    public function testMissingGroupId()
    {
        $request = $this->createRequest('/groups', ['X-User-Id' => 1]);
        $handler = $this->createHandlerMock(false);

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }
}