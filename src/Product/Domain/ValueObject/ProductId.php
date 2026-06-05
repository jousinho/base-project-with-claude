<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use App\Shared\Domain\ValueObject\Uuid;
use Symfony\Component\Uid\Uuid as SymfonyUuid;

final class ProductId extends Uuid
{
    public static function generate(): self
    {
        return new self(SymfonyUuid::v4()->toRfc4122());
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }
}
