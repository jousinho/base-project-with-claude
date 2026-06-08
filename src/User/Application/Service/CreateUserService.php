<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Application\Command\CreateUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\DuplicateEmailException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserName;
use Symfony\Component\Messenger\MessageBusInterface;

final class CreateUserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly MessageBusInterface $eventBus,
    ) {}

    public function execute(CreateUserCommand $command): string
    {
        $this->guardEmailIsUnique($command->email);

        $id   = UserId::generate();
        $user = User::create(
            $id,
            Email::fromString($command->email),
            UserName::fromString($command->name),
        );

        $this->userRepository->save($user);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $id->value();
    }

    private function guardEmailIsUnique(string $email): void
    {
        $existing = $this->userRepository->findByEmail(Email::fromString($email));

        if ($existing !== null) {
            throw DuplicateEmailException::withEmail($email);
        }
    }
}
