<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use RuntimeException;

final class UserNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('User "%s" not found.', $id));
    }
}
