<?php

namespace App\Controllers;

use App\Models\Message;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class MessageController
{
    private Message $messageModel;

    public function __construct(Message $messageModel)
    {
        $this->messageModel = $messageModel;
    }

    // send message to a group
    public function sendMessage(Request $request, Response $response, $args)
    {
        $content = $request->getParsedBody()['content'];
        $userId = $request->getHeaderLine('X-User-Id');
        $groupId = $args['groupId'];

        // check if message is empty
        if (!$content) {
            return $this->jsonResponse($response, ['error' => 'Message cannot be empty'], 400);
        }

        $messageId = $this->messageModel->create($groupId, $userId, $content);
        if (empty($messageId)) {
            return $this->jsonResponse($response, ['error' => 'Unknown error occurred'], 500);
        }

        return $this->jsonResponse($response, ['message' => 'Message sent successfully']);
    }

    public function getMessagesByGroup(Request $request, Response $response, $args)
    {
        $groupId = $args['groupId'];
        $queryParams = $request->getQueryParams();
        $since = $queryParams['since'] ?? null;
        $messages = $this->messageModel->getByGroup($groupId, $since);
        return $this->jsonResponse($response, $messages);
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): MessageInterface|Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}