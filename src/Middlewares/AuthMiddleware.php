<?php

namespace App\Middlewares;

use PDO;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response as SlimResponse;

class AuthMiddleware implements MiddlewareInterface
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function process(Request $request, RequestHandler $handler): Response
    {
        $authHeader = $request->getHeaderLine('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $this->unauthorizedResponse("Missing or invalid token");
        }

        $token = $matches[1]; // get bearer token
        $userId = $request->getQueryParams()['userId'] ?? null;
        $user = $this->verifyToken($token, $userId);

        if (!$user) {
            return $this->unauthorizedResponse("Invalid token");
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

    private function unauthorizedResponse(string $message): Response
    {
        $response = new SlimResponse();
        $response->getBody()->write(json_encode(['error' => $message]));
        return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
    }
}
