<?php


use App\Middlewares\GroupPermissionMiddleware;
use App\Models\Group;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class GroupPermissionMiddlewareTest extends TestCase
{
    private PDO $pdo;
    private Group $groupModel;
    private GroupPermissionMiddleware $middleware;

    protected function setUp(): void
    {
        // 使用 SQLite 内存数据库
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // 创建测试表并插入测试数据
        $this->pdo->exec("
            CREATE TABLE user_groups
            (
                id        INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id   INTEGER NOT NULL,
                group_id  INTEGER NOT NULL,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
                FOREIGN KEY (user_id) REFERENCES users (id),
                FOREIGN KEY (group_id) REFERENCES groups (id)
            );
            
            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1)
            ");

        // 初始化 Group 模型
        $this->groupModel = new Group($this->pdo);

        // 初始化中间件
        $this->middleware = new GroupPermissionMiddleware($this->groupModel);
    }

    public function testUserInGroup()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/1')
            ->withHeader('X-User-Id', 1);

        // Mock RequestHandler
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn((new ResponseFactory())->createResponse(200));

        // 调用中间件
        $response = $this->middleware->__invoke($request, $handler);

        // 断言 HTTP 状态码
        $this->assertEquals(200, $response->getStatusCode());

        // 确保返回 Body 为空（成功通过）
        $responseBody = (string) $response->getBody();
        $this->assertEmpty($responseBody);
    }

    public function testUserNotInGroup()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/2')
            ->withHeader('X-User-Id', 1);

        // Mock RequestHandler
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        // 调用中间件
        $response = $this->middleware->__invoke($request, $handler);

        // 断言 HTTP 状态码
        $this->assertEquals(403, $response->getStatusCode());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }
    public function testMissingUserId()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/1');

        // Mock RequestHandler
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        // 调用中间件
        $response = $this->middleware->__invoke($request, $handler);

        // 断言 HTTP 状态码
        $this->assertEquals(403, $response->getStatusCode());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }

    public function testMissingGroupId()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups')
            ->withHeader('X-User-Id', 1);

        // Mock RequestHandler
        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->never())
            ->method('handle');

        // 调用中间件
        $response = $this->middleware->__invoke($request, $handler);

        // 断言 HTTP 状态码
        $this->assertEquals(403, $response->getStatusCode());

        $this->assertEquals(403, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"User is not in the group"}',
            (string)$response->getBody()
        );
    }
}