<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final class GetProductCommand
{
    public function __construct(
        public readonly string $id,
    ) {}
}
