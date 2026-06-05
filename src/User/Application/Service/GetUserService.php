<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\Command\GetUserCommand;
use App\User\Application\DTO\UserDTO;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\UserId;

final class GetUserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {}

    public function execute(GetUserCommand $command): UserDTO
    {
        $user = $this->userRepository->findById(UserId::fromString($command->id));

        if ($user === null) {
            throw UserNotFoundException::withId($command->id);
        }

        return new UserDTO(
            id:     $user->id()->value(),
            email:  $user->email()->value(),
            name:   $user->name()->value(),
            status: $user->status()->value,
        );
    }
}
