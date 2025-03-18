<?php
namespace App\Controllers;

use App\Models\Message;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageController {
    private Message $messageModel;

    public function __construct(Message $messageModel) {
        $this->messageModel = $messageModel;
    }

    // 发送消息
    public function sendMessage(Request $request, Response $response, $args) {
        $data = $request->getParsedBody();
        $userId = $data['userId'] ?? null;
        $groupId = $args['groupId'] ?? null;
        $content = $data['content'];

        $messageId = $this->messageModel->create($groupId, $userId, $content);
        $response->getBody()->write(json_encode(['id' => $messageId]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // 获取群组消息
    public function getMessagesByGroup(Response $response, $args) {
        $groupId = $args['groupId'];
        $messages = $this->messageModel->getByGroup($groupId);
        $response->getBody()->write(json_encode($messages));
        return $response->withHeader('Content-Type', 'application/json');
    }
}