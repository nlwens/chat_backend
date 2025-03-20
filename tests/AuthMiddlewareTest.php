<?php

namespace Tests\Middlewares;

use App\Middlewares\AuthMiddleware;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class AuthMiddlewareTest extends TestCase
{
    private PDO $pdo;
    private AuthMiddleware $middleware;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, token TEXT)');
        $this->pdo->exec("INSERT INTO users (id, token) VALUES (1, 'valid-token')");

        $this->middleware = new AuthMiddleware($this->pdo);
    }

    public function testValidToken()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/users/1')
            ->withHeader('Authorization', 'Bearer valid-token');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $handler->expects($this->once())
            ->method('handle')
            ->willReturn((new ResponseFactory())->createResponse());

        $response = $this->middleware->__invoke($request, $handler);

        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingAuthorizationHeader()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/users/1');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->__invoke($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"Missing or invalid token"}',
            (string)$response->getBody()
        );
    }

    public function testInvalidBearerTokenFormat()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/users/1')
            ->withHeader('Authorization', 'InvalidTokenFormat');
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->__invoke($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"Missing or invalid token"}',
            (string)$response->getBody()
        );
    }

    public function testInvalidToken()
    {
        $request = (new RequestFactory())->createRequest('GET', '/groups/users/1')
            ->withHeader('Authorization', 'Bearer invalid-token')
            ->withQueryParams(['userId' => 1]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->__invoke($request, $handler);

        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"Missing or invalid token"}',
            (string)$response->getBody()
        );
    }
}