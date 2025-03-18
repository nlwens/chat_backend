<?php

namespace App\Controllers;

use App\Models\User;
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
    public function create(Request $request, Response $response): \Psr\Http\Message\MessageInterface|Response
    {
        $data = $request->getParsedBody();
        $userName = $data['username'];
        $token = bin2hex(random_bytes(16));
        $userId = $this->userModel->create($userName, $token);

        $response->getBody()->write(json_encode([
            'id' => $userId,
            'username' => $userName,
            'token' => $token
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }
}