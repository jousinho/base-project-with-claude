<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use InvalidArgumentException;

final class ProductName
{
    private function __construct(private readonly string $value) {}

    public static function fromString(string $value): self
    {
        if (trim($value) === '') {
            throw new InvalidArgumentException('ProductName cannot be empty.');
        }

        if (strlen($value) > 200) {
            throw new InvalidArgumentException('ProductName cannot exceed 200 characters.');
        }

        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }
}
