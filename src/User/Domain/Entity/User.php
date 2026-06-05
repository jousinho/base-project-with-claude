<?php

declare(strict_types=1);

namespace App\User\Domain\Entity;

use App\User\Domain\Event\UserWasCreated;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserName;
use App\User\Domain\ValueObject\UserStatus;

final class User
{
    private array $domainEvents = [];

    private function __construct(
        private string $id,
        private string $email,
        private string $name,
        private string $status,
    ) {}

    public static function create(UserId $id, Email $email, UserName $name): self
    {
        $user = new self(
            $id->value(),
            $email->value(),
            $name->value(),
            UserStatus::Active->value,
        );

        $user->domainEvents[] = new UserWasCreated($id, $email, $name);

        return $user;
    }

    public function id(): UserId
    {
        return UserId::fromString($this->id);
    }

    public function email(): Email
    {
        return Email::fromString($this->email);
    }

    public function name(): UserName
    {
        return UserName::fromString($this->name);
    }

    public function status(): UserStatus
    {
        return UserStatus::from($this->status);
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
