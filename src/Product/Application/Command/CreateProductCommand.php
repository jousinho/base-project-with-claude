<?php

declare(strict_types=1);

namespace App\Product\Application\Command;

final class CreateProductCommand
{
    public function __construct(
        public readonly string $name,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
    ) {}
}
