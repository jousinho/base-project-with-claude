<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\DTO\UserDTO;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;

final class ListUsersService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    /** @return UserDTO[] */
    public function execute(): array
    {
        return array_map(
            fn(User $user) => new UserDTO(
                id:     $user->id()->value(),
                email:  $user->email()->value(),
                name:   $user->name()->value(),
                status: $user->status()->value,
            ),
            $this->userRepository->findAll(),
        );
    }
}
