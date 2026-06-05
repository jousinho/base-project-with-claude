<?php

declare(strict_types=1);

namespace App\Product\Application\Service;

use App\Product\Application\DTO\ProductDTO;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;

final class ListProductsService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    /** @return ProductDTO[] */
    public function execute(): array
    {
        return array_map(
            fn(Product $product) => new ProductDTO(
                id:            $product->id()->value(),
                name:          $product->name()->value(),
                priceAmount:   $product->price()->amount(),
                priceCurrency: $product->price()->currency()->value,
            ),
            $this->productRepository->findAll(),
        );
    }
}
