<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Shared\Domain\Event\DomainEvent;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\UserId;
use App\User\Domain\ValueObject\UserName;

final class UserWasCreated extends DomainEvent
{
    public function __construct(
        public readonly UserId $userId,
        public readonly Email $email,
        public readonly UserName $name,
    ) {
        parent::__construct();
    }
}
