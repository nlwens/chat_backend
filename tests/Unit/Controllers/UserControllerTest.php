<?php

namespace Unit\Controllers;

use App\Controllers\UserController;
use App\Models\User;
use PDO;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Request;

class UserControllerTest extends TestCase
{
    private UserController $controller;
    private User $user;
    private PDO $pdo;
    private RequestFactory $requestFactory;
    private StreamFactory $streamFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS users
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                username   TEXT NOT NULL,
                token      TEXT NOT NULL UNIQUE,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW'))
            );
        ");

        $this->user = new User($this->pdo);
        $this->controller = new UserController($this->user);

        $this->requestFactory = new RequestFactory();
        $this->streamFactory = new StreamFactory();
        $this->responseFactory = new ResponseFactory();
    }

    private function createRequest($method, $uri, $headers, $body): Request
    {
        $request = $this->requestFactory->createRequest($method, $uri);
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }
        if ($body !== null) {
            $stream = $this->streamFactory->createStream(json_encode($body));
            $request = $request->withBody($stream);
        }
        return $request->withParsedBody(
            $body !== null ? json_decode($request->getBody()->getContents(), true) : null
        );
    }

    /**
     * @throws RandomException
     */
    public function testAddNewUser()
    {
        $request = $this->createRequest(
            'POST',
            '/users',
            ['Content-Type' => 'application/json'],
            ['username' => 'User2']
        );

        $response = $this->controller->create(
            $request,
            $this->responseFactory->createResponse()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $userList = $this->user->getAllUsers();
        $this->assertEquals('User2', end($userList)['username']);
    }

    public function testAddNewUserWithoutUsername()
    {
        $request = $this->createRequest('POST', '/users', [], []);

        $response = $this->controller->create(
            $request,
            $this->responseFactory->createResponse()
        );

        $this->assertEquals(400, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username cannot be empty', $responseData['error']);
    }
}