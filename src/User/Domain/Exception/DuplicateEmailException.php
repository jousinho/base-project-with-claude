<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use RuntimeException;

final class DuplicateEmailException extends RuntimeException
{
    public static function withEmail(string $email): self
    {
        return new self(sprintf('Email "%s" is already in use.', $email));
    }
}
