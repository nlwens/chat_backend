<?php

namespace Unit\Middleware;

use App\Middlewares\AuthMiddleware;
use PDO;
use PHPUnit\Framework\TestCase;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Request;

class AuthMiddlewareTest extends TestCase
{
    private PDO $pdo;
    private AuthMiddleware $middleware;
    private RequestFactory $requestFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec('CREATE TABLE users (id INTEGER PRIMARY KEY, token TEXT)');
        $this->pdo->exec("INSERT INTO users (id, token) VALUES (1, 'valid-token')");

        $this->middleware = new AuthMiddleware($this->pdo);

        $this->requestFactory = new RequestFactory();
        $this->responseFactory = new ResponseFactory();
    }

    private function createRequest($headers): Request
    {
        $request = $this->requestFactory->createRequest('GET', '/groups');
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        return $request;
    }

    public function testValidToken()
    {
        $request = $this->createRequest([
            'X-User-Id' => 1,
            'Authorization' => 'Bearer valid-token'
        ]);

        $handler = $this->createMock(RequestHandlerInterface::class);
        $handler->expects($this->once())
            ->method('handle')
            ->willReturn($this->responseFactory->createResponse());

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testMissingAuthorizationHeader()
    {
        $request = $this->createRequest(['X-User-Id' => 1]);
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
        $request = $this->createRequest([
            'X-User-Id' => 1,
            'Authorization' => 'Bearer invalid-token'
        ]);
        $handler = $this->createMock(RequestHandlerInterface::class);

        $response = $this->middleware->__invoke($request, $handler);
        $this->assertEquals(401, $response->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            '{"error":"Missing or invalid token"}',
            (string)$response->getBody()
        );
    }
}