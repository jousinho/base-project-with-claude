<?php

declare(strict_types=1);

namespace App\Product\Application\Service;

use App\Product\Application\Command\GetProductCommand;
use App\Product\Application\DTO\ProductDTO;
use App\Product\Domain\Exception\ProductNotFoundException;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\ProductId;

final class GetProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(GetProductCommand $command): ProductDTO
    {
        $product = $this->productRepository->findById(ProductId::fromString($command->id));

        if ($product === null) {
            throw ProductNotFoundException::withId($command->id);
        }

        return new ProductDTO(
            id:            $product->id()->value(),
            name:          $product->name()->value(),
            priceAmount:   $product->price()->amount(),
            priceCurrency: $product->price()->currency()->value,
        );
    }
}
