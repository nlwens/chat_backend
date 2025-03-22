<?php

namespace Unit\Controllers;

use App\Controllers\GroupController;
use App\Models\Group;
use Exception;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Request;

class GroupControllerTest extends TestCase
{
    private GroupController $controller;
    private Group $group;
    private PDO $pdo;
    private RequestFactory $requestFactory;
    private StreamFactory $streamFactory;
    private ResponseFactory $responseFactory;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS groups 
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                group_name TEXT NOT NULL,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW'))
            );

            CREATE TABLE user_groups
            (
            id        INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id   INTEGER NOT NULL,
            group_id  INTEGER NOT NULL,
            created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW'))
            );

            INSERT INTO groups (group_name) VALUES ('Group 1');
            INSERT INTO groups (group_name) VALUES ('Group 2');
            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
        ");

        $this->group = new Group($this->pdo);
        $this->controller = new GroupController($this->group);

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
     * @throws Exception
     */
    public function testCreateGroup()
    {
        $request = $this->createRequest(
            'POST',
            '/groups',
            ['X-User-Id' => 1, 'Content-Type' => 'application/json'],
            ['group_name' => 'Test Group']
        );

        $response = $this->controller->create(
            $request,
            $this->responseFactory->createResponse()
        );

        $this->assertEquals(200, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('group_id', $responseData);

        $groupList = $this->group->getAll();
        $this->assertEquals('Test Group', end($groupList)['group_name']);
    }

    public function testUserJoinAGroup()
    {
        $request = $this->createRequest(
            'POST',
            '/groups/2/members',
            ['X-User-Id' => 1],
            null
        );

        $response = $this->controller->joinGroup(
            $request,
            $this->responseFactory->createResponse(),
            ['groupId' => 2]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User successfully joined the group', $responseData['message']);

        $result = $this->group->isUserInGroup(1, 2);
        $this->assertTrue($result);
    }

    public function testUserJoinWhenAlreadyInGroup()
    {
        $request = $this->createRequest(
            'POST',
            '/groups/1/members',
            ['X-User-Id' => 1],
            null
        );

        $response = $this->controller->joinGroup(
            $request,
            $this->responseFactory->createResponse(),
            ['groupId' => 1]
        );

        $this->assertEquals(409, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('User is already in the group', $responseData['error']);
    }

    public function testUserJoinGroupNotExists()
    {
        $request = $this->createRequest(
            'POST',
            '/groups/10/members',
            ['X-User-Id' => 1],
            null
        );

        $response = $this->controller->joinGroup(
            $request,
            $this->responseFactory->createResponse(),
            ['groupId' => 10]
        );

        $this->assertEquals(404, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Cannot find the group', $responseData['error']);
    }

    public function testUserLeaveAGroup()
    {
        $request = $this->createRequest(
            'POST',
            '/groups/1/members',
            ['X-User-Id' => 1],
            null
        );

        $response = $this->controller->leaveGroup(
            $request,
            $this->responseFactory->createResponse(),
            ['groupId' => 1]
        );

        $this->assertEquals(200, $response->getStatusCode());
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User successfully left the group', $responseData['message']);

        $result = $this->group->isUserInGroup(1, 1);
        $this->assertFalse($result);
    }
}