<?php

declare(strict_types=1);

namespace App\User\Application\Command;

final class GetUserCommand
{
    public function __construct(
        public readonly string $id,
    ) {}
}
