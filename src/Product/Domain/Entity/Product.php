<?php

declare(strict_types=1);

namespace App\Product\Domain\Entity;

use App\Product\Domain\Event\ProductWasCreated;
use App\Product\Domain\ValueObject\Currency;
use App\Product\Domain\ValueObject\Money;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;

class Product
{
    private array $domainEvents = [];

    private function __construct(
        private string $id,
        private string $name,
        private int $priceAmount,
        private string $priceCurrency,
    ) {}

    public static function create(ProductId $id, ProductName $name, Money $price): self
    {
        $product = new self(
            $id->value(),
            $name->value(),
            $price->amount(),
            $price->currency()->value,
        );

        $product->domainEvents[] = new ProductWasCreated($id, $name, $price);

        return $product;
    }

    public function id(): ProductId
    {
        return ProductId::fromString($this->id);
    }

    public function name(): ProductName
    {
        return ProductName::fromString($this->name);
    }

    public function price(): Money
    {
        return Money::of($this->priceAmount, Currency::from($this->priceCurrency));
    }

    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }
}
