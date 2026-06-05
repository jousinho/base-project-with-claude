<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use InvalidArgumentException;

final class UserName
{
    private function __construct(private readonly string $value) {}

    public static function fromString(string $value): self
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('UserName cannot be empty.');
        }

        if (strlen($value) > 100) {
            throw new InvalidArgumentException('UserName cannot exceed 100 characters.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
