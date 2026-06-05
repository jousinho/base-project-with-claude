<?php

declare(strict_types=1);

namespace App\Product\Application\Service;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Domain\Entity\Product;
use App\Product\Domain\Repository\ProductRepositoryInterface;
use App\Product\Domain\ValueObject\Currency;
use App\Product\Domain\ValueObject\Money;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;

final class CreateProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {}

    public function execute(CreateProductCommand $command): string
    {
        $id      = ProductId::generate();
        $product = Product::create(
            $id,
            ProductName::fromString($command->name),
            $this->buildPrice($command),
        );

        $this->productRepository->save($product);

        return $id->value();
    }

    private function buildPrice(CreateProductCommand $command): Money
    {
        $currency = Currency::from($command->priceCurrency);

        return Money::of($command->priceAmount, $currency);
    }
}
