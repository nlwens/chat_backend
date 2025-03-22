<?php

namespace Unit\Controllers;

use App\Controllers\MessageController;
use App\Models\Message;
use PDO;
use PHPUnit\Framework\TestCase;
use Slim\Psr7\Factory\RequestFactory;
use Slim\Psr7\Factory\ResponseFactory;
use Slim\Psr7\Factory\StreamFactory;
use Slim\Psr7\Request;

class MessageControllerTest extends TestCase
{
    private MessageController $controller;
    private Message $message;
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
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                name TEXT NOT NULL
            );

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

            CREATE TABLE IF NOT EXISTS user_groups
            (
                id         INTEGER PRIMARY KEY AUTOINCREMENT,
                user_id    INTEGER NOT NULL,
                group_id   INTEGER NOT NULL,
                created_at DATETIME DEFAULT (STRFTIME('%Y-%m-%dT%H:%M:%fZ', 'NOW')),
                FOREIGN KEY (user_id) REFERENCES users (id),
                FOREIGN KEY (group_id) REFERENCES groups (id)
            );

            INSERT INTO groups (id, name) VALUES (1, 'Test Group');
            INSERT INTO users (id, username, token) VALUES (1, 'test_user', 'test_token');
            INSERT INTO user_groups (user_id, group_id) VALUES (1, 1);
            INSERT INTO messages (group_id, user_id, content, created_at) VALUES (1,1,'Test Message old', '2023-01-01T00:00:00Z');
            INSERT INTO messages (group_id, user_id, content, created_at) VALUES (1,1,'Test Message new', '2025-01-01T00:00:00Z');
        ");

        $this->message = new Message($this->pdo);
        $this->controller = new MessageController($this->message);

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
        return $request->withParsedBody($body);
    }

    public function testSendMessage()
    {
        $request = $this->createRequest('POST', '/groups/1/messages', [
            'X-User-Id' => '1',
            'Content-Type' => 'application/json'
        ], ['content' => 'Test Message 2']);

        $args = ['groupId' => 1];
        $response = $this->controller->sendMessage($request, $this->responseFactory->createResponse(), $args);

        // check http status code
        $this->assertEquals(200, $response->getStatusCode());

        // check returned message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertEquals('Message sent successfully', $responseData['message']);

        // check if message successfully recorded in database
        $messageList = $this->message->getByGroup(1, null);
        $this->assertEquals('Test Message 2', end($messageList)['content']);
    }

    public function testSendEmptyMessage()
    {
        $request = $this->createRequest('POST', '/groups/1/messages', [
            'X-User-Id' => '1',
            'Content-Type' => 'application/json',
        ], null);

        $args = ['groupId' => 1];
        $response = $this->controller->sendMessage($request, $this->responseFactory->createResponse(), $args);

        // check http status code
        $this->assertEquals(400, $response->getStatusCode());

        // check returned message
        $responseBody = (string)$response->getBody();
        $this->assertJson($responseBody);
        $responseData = json_decode($responseBody, true);
        $this->assertArrayHasKey('error', $responseData);
        $this->assertEquals('Message cannot be empty', $responseData['error']);
    }

    public function testGetMessage()
    {
        $request = $this->createRequest('GET', '/groups/1/messages', [
            'X-User-Id' => '1',
            'Content-Type' => 'application/json'
        ], null);

        $args = ['groupId' => 1];
        $response = $this->controller->getMessagesByGroup($request, $this->responseFactory->createResponse(), $args);

        // check http status code
        $this->assertEquals(200, $response->getStatusCode());

        // check returned message
        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);
        $this->assertIsArray($responseData);
        $this->assertEquals('Test Message old', $responseData[0]['content']);
        $this->assertEquals('Test Message new', $responseData[1]['content']);

        // set since timestamp
        $request = $this->createRequest('GET', '/groups/1/messages?since=2024-01-01', [
            'X-User-Id' => '1',
            'Content-Type' => 'application/json'
        ], null);
        $response = $this->controller->getMessagesByGroup($request, $this->responseFactory->createResponse(), $args);

        $responseBody = (string)$response->getBody();
        $responseData = json_decode($responseBody, true);
        $this->assertEquals('Test Message new', $responseData[0]['content']);
        $this->assertCount(1, $responseData);
    }
}