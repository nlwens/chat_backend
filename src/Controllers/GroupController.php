<?php

namespace App\Controllers;

use App\Models\Group;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class GroupController
{
    private Group $groupModel;

    public function __construct(Group $groupModel)
    {
        $this->groupModel = $groupModel;
    }

    public function getAll(Request $request, Response $response): MessageInterface|Response
    {
        $groups = $this->groupModel->getAll();
        return $this->jsonResponse($response, $groups);
    }

    // create a new group
    public function create(Request $request, Response $response): MessageInterface|Response
    {
        $data = $request->getParsedBody();
        $groupName = $data['group_name'];

        $groupId = $this->groupModel->create($groupName);
        return $this->jsonResponse($response, ['group_id' => $groupId]);
    }

    public function joinGroup(Request $request, Response $response, $args): MessageInterface|Response
    {
        return $this->modifyGroupMembership($request, $response, $args, 'join');
    }

    public function leaveGroup(Request $request, Response $response, $args): MessageInterface|Response
    {
        return $this->modifyGroupMembership($request, $response, $args, 'leave');
    }

    /* It is not necessary to check if the user really exists in the users table
     * because it can't pass the AuthMiddleware
     */
    private function modifyGroupMembership(Request $request, Response $response, $args, $action): MessageInterface|Response
    {
        $userId = $request->getHeaderLine('X-User-Id');
        $groupId = $args['groupId'] ?? null;

        if (!$userId || !$groupId) {
            return $this->jsonResponse($response, ['error' => 'User ID and Group ID are required'], 400);
        }

        try {
            $success = ($action === 'join')
                ? $this->groupModel->addUserToGroup($userId, $groupId)
                : $this->groupModel->deleteUserFromGroup($userId, $groupId);

            if ($success) {
                $status = ($action === 'join') ? 'joined' : 'left';
                return $this->jsonResponse($response, ['message' => "User successfully {$status} the group"]);
            }
        } catch (\Exception $e) {
            return $this->jsonResponse($response, ['error' => $e->getMessage()], 400);
        }

        return $this->jsonResponse($response, ['error' => 'Unknown error occurred'], 500);
    }

    private function jsonResponse(Response $response, array $data, int $status = 200): MessageInterface|Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }
}