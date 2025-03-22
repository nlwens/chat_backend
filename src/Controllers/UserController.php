<?php

namespace App\Controllers;

use App\Models\User;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Random\RandomException;

class UserController
{
    private User $userModel;

    public function __construct(User $userModel)
    {
        $this->userModel = $userModel;
    }

    /**
     * @throws RandomException
     */
    public function create(Request $request, Response $response): MessageInterface|Response
    {
        $data = $request->getParsedBody();
        $username = $data['username'];

        if (!$username) {
            $errorMsg = ['error' => 'Username cannot be empty'];
            return $this->jsonResponse($response, $errorMsg,400);
        }

        $token = bin2hex(random_bytes(16));
        $userId = $this->userModel->create($username, $token);

        $data = [
            'id' => $userId,
            'username' => $username,
            'token' => $token
        ];
        return $this->jsonResponse($response, $data, 200);
    }

    private function jsonResponse(Response $response, $data, $status): MessageInterface|Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}