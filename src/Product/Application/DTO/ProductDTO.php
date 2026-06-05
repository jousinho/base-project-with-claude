<?php

declare(strict_types=1);

namespace App\Product\Application\DTO;

final class ProductDTO
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly int $priceAmount,
        public readonly string $priceCurrency,
    ) {}
}
