<?php

namespace App\Controllers;

use App\Models\Message;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageController
{
    private Message $messageModel;

    public function __construct(Message $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    // 发送消息
    public function sendMessage(Request $request, Response $response, $args)
    {
        $content = $request->getParsedBody()['content'];
        $userId = $request->getQueryParams()['userId'];
        $groupId = $args['groupId'];

        // check if message is empty
        if (!$content) {
            $response = new \Slim\Psr7\Response();
            $response->getBody()->write(json_encode(['error' => 'Message cannot be empty']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $messageId = $this->messageModel->create($groupId, $userId, $content);
        $response->getBody()->write(json_encode(['id' => $messageId]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 获取群组消息
    public function getMessagesByGroup(Request $request, Response $response, $args)
    {
        $groupId = $args['groupId'];
        $messages = $this->messageModel->getByGroup($groupId);
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }
}