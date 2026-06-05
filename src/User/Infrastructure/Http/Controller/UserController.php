<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Http\Controller;

use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Command\GetUserCommand;
use App\User\Application\Service\CreateUserService;
use App\User\Application\Service\GetUserService;
use App\User\Application\Service\ListUsersService;
use App\User\Domain\Exception\DuplicateEmailException;
use App\User\Domain\Exception\UserNotFoundException;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class UserController
{
    public function __construct(
        private readonly CreateUserService $createUser,
        private readonly GetUserService $getUser,
        private readonly ListUsersService $listUsers,
    ) {}

    #[Route('/api/users', methods: ['POST'])]
    public function create(Request $request): JsonResponse
    {
        $data    = json_decode($request->getContent(), true) ?? [];
        $command = new CreateUserCommand(
            email: $data['email'] ?? '',
            name:  $data['name'] ?? '',
        );

        try {
            $id = $this->createUser->execute($command);
        } catch (InvalidArgumentException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (DuplicateEmailException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_CONFLICT);
        }

        return new JsonResponse(null, Response::HTTP_CREATED, [
            'Location' => '/api/users/' . $id,
        ]);
    }

    #[Route('/api/users/{id}', methods: ['GET'])]
    public function get(string $id): JsonResponse
    {
        try {
            $dto = $this->getUser->execute(new GetUserCommand($id));
        } catch (UserNotFoundException $e) {
            return new JsonResponse(['error' => $e->getMessage()], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'id'     => $dto->id,
            'email'  => $dto->email,
            'name'   => $dto->name,
            'status' => $dto->status,
        ]);
    }

    #[Route('/api/users', methods: ['GET'])]
    public function list(): JsonResponse
    {
        $dtos = $this->listUsers->execute();

        return new JsonResponse(array_map(
            fn($dto) => ['id' => $dto->id, 'email' => $dto->email, 'name' => $dto->name, 'status' => $dto->status],
            $dtos,
        ));
    }
}
