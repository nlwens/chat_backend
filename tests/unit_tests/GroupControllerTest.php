<?php

use App\Controllers\GroupController;
use App\Models\Group;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;

class GroupControllerTest extends TestCase
{
    private GroupController $controller;
    private Group $group;
    private PDO $pdo;

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
            created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
            FOREIGN KEY (user_id) REFERENCES users (id),
            FOREIGN KEY (group_id) REFERENCES groups (id)
            );

            INSERT INTO groups (group_name) VALUES ('Group 1');
            INSERT INTO groups (group_name) VALUES ('Group 2');
            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
        ");

        $this->group = new Group($this->pdo);
        $this->controller = new GroupController($this->group);
    }

    public function testCreateGroup()
    {
        $factory = new RequestFactory();
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['group_name' => 'Test Group']));
        $request = $factory->createRequest('POST', '/groups')
            ->withHeader('X-User-Id', 1)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->create($request, $response);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('group_id', $responseData);

        // check the last group is the new added group
        $groupList = $this->group->getAll();
        $this->assertEquals('Test Group', end($groupList)['group_name']);
    }

    public function testUserJoinAGroup()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('POST', '/groups/2/members')
            ->withHeader('X-User-Id', 1);
        $args = [
            'groupId' => 2,
        ];
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();

        $response = $this->controller->joinGroup($request, $response, $args);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User successfully joined the group', $responseData['message']);

        // check user is in group
        $result = $this->group->isUserInGroup(1,2);
        $this->assertTrue($result);
    }

    public function testUserJoinWhenAlreadyInGroup()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('POST', '/groups/1/members')
            ->withHeader('X-User-Id', 1);
        $args = [
            'groupId' => 1,
        ];

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->joinGroup($request, $response, $args);

        // check http code
        $this->assertEquals(409, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('User is already in the group', $responseData['error']);
    }

    public function testUserJoinGroupNotExists()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('POST', '/groups/10/members')
            ->withHeader('X-User-Id', 1);
        $args = [
            'groupId' => 10,
        ];

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->joinGroup($request, $response, $args);

        // check http code
        $this->assertEquals(404, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Cannot find the group', $responseData['error']);
    }

    public function testUserLeaveAGroup()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('POST', '/groups/1/members')
            ->withHeader('X-User-Id', 1);
        $args = [
            'groupId' => 1,
        ];

        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->leaveGroup($request, $response, $args);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('User successfully left the group', $responseData['message']);

        // check user no longer in the group
        $result = $this->group->isUserInGroup(1,1);
        $this->assertFalse($result);
    }
}