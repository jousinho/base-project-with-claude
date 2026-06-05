<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

abstract class Uuid
{
    public function __construct(protected readonly string $value) {}

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
