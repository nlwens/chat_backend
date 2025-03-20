<?php

use App\Controllers\UserController;
use App\Models\User;
use Random\RandomException;
use Slim\Psr7\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class UserControllerTest extends TestCase
{
    private UserController $controller;
    private User $user;
    private PDO $pdo;

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
    }

    /**
     * @throws RandomException
     */
    public function testAddNewUser()
    {
        $factory = new RequestFactory();
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['username' => 'User2']));
        $request = $factory->createRequest('POST', '/users')
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->create($request, $response);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check user successfully added
        $userList = $this->user->getAllUsers();
        $this->assertEquals('User2', end($userList)['username']);
    }

    public function testAddNewUserWithoutUsername()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('POST', '/users');

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->create($request, $response);

        // check http code
        $this->assertEquals(400, $response->getStatusCode());

        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Username cannot be empty', $responseData['error']);
    }
}