<?php

namespace App\Middlewares;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function __invoke(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');
        preg_match('/Bearer\s+(\S+)/', $authHeader, $matches);
        $token = $matches[1]; // get bearer token

        $userId = $request->getHeaderLine('X-User-Id');

        $user = $this->verifyToken($token, $userId);
        if (!$user) {
            return $this->unauthorizedResponse();
        }

        return $handler->handle($request);
    }

    private function verifyToken($token, $userId)
    {
        $stmt = $this->pdo->prepare("SELECT id FROM users WHERE id = :userId AND token = :token");
        $stmt->execute([
            'userId' => $userId,
            'token' => $token
        ]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function unauthorizedResponse(): Response
    {
        if (ob_get_length()) ob_clean();

        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => 'Missing or invalid token']));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
