<?php

use App\Controllers\MessageController;
use App\Models\Message;
use Slim\Psr7\Factory\StreamFactory;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;

class MessageControllerTest extends TestCase
{
    private MessageController $controller;
    private Message $message;
    private PDO $pdo;

    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS messages
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                group_id   INTEGER NOT NULL,
                user_id    INTEGER NOT NULL,
                content    TEXT    NOT NULL,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
                FOREIGN KEY (group_id) REFERENCES groups (id),
                FOREIGN KEY (user_id) REFERENCES users (id)
            );

            CREATE TABLE IF NOT EXISTS users
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                username   TEXT NOT NULL,
                token      TEXT NOT NULL UNIQUE,
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

            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
            INSERT INTO messages (group_id, user_id, content, created_at) VALUES (1,1,'Test Message old', '2023-01-01T00:00:00Z');
            INSERT INTO messages (group_id, user_id, content, created_at) VALUES (1,1,'Test Message new', '2025-01-01T00:00:00Z');
        ");

        $this->message = new Message($this->pdo);
        $this->controller = new MessageController($this->message);
    }

    public function testSendMessage()
    {
        $factory = new RequestFactory();
        $streamFactory = new StreamFactory();
        $body = $streamFactory->createStream(json_encode(['content' => 'Test Message 2']));
        $request = $factory->createRequest('POST', '/groups/1/messages')
            ->withHeader('X-User-Id', 1)
            ->withHeader('Content-Type', 'application/json')
            ->withBody($body);

        $args = ['groupId' => 1];
        $request = $request->withParsedBody(json_decode($request->getBody()->getContents(), true));
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->sendMessage($request, $response, $args);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check return message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Message sent successfully', $responseData['message']);

        // check the message successfully added to the end
        $messageList = $this->message->getByGroup(1, null);
        $this->assertEquals('Test Message 2', end($messageList)['content']);
    }

    public function testGetMessage()
    {
        $factory = new RequestFactory();
        $request = $factory->createRequest('GET', '/groups/1/messages')
            ->withHeader('X-User-Id', 1)
            ->withHeader('Content-Type', 'application/json');

        $args = ['groupId' => 1];
        $responseFactory = new ResponseFactory();
        $response = $responseFactory->createResponse();
        $response = $this->controller->getMessagesByGroup($request, $response, $args);

        // check http code
        $this->assertEquals(200, $response->getStatusCode());

        // check if messages are correct
        $responseBody = $response->getBody();
        $responseData = json_decode($responseBody, true);
        $this->assertIsArray($responseData);
        $this->assertEquals('Test Message old', $responseData[0]['content']);
        $this->assertEquals('Test Message new', $responseData[1]['content']);

        // test if parameter since works
        $request = $factory->createRequest('GET', '/groups/1/messages?since=2024-01-01')
            ->withHeader('X-User-Id', 1)
            ->withHeader('Content-Type', 'application/json');
        $response = $responseFactory->createResponse();
        $response = $this->controller->getMessagesByGroup($request, $response, $args);
        $responseBody = $response->getBody();
        $responseData = json_decode($responseBody, true);

        // only the messages created after 2024-01-01 will be returned
        $this->assertEquals('Test Message new', $responseData[0]['content']);
        $this->assertCount(1, $responseData);
    }
}