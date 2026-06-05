<?php

declare(strict_types=1);

namespace App\Product\Domain\Exception;

use RuntimeException;

final class ProductNotFoundException extends RuntimeException
{
    public static function withId(string $id): self
    {
        return new self(sprintf('Product "%s" not found.', $id));
    }
}
