<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Domain\Entity;

use App\Product\Domain\Entity\Product;
use App\Product\Domain\Event\ProductWasCreated;
use App\Product\Domain\ValueObject\Currency;
use App\Product\Domain\ValueObject\Money;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use PHPUnit\Framework\TestCase;

final class ProductTest extends TestCase
{
    public function test_create_product__should_emit_product_was_created_event_with_correct_data(): void
    {
        $id    = ProductId::generate();
        $name  = ProductName::fromString('Widget');
        $price = Money::of(1000, Currency::EUR);

        $product = Product::create($id, $name, $price);
        $events  = $product->pullDomainEvents();

        $this->assertCount(1, $events);
        $this->assertInstanceOf(ProductWasCreated::class, $events[0]);
        $this->assertSame($id->value(), $events[0]->productId->value());
        $this->assertSame('Widget', $events[0]->name->value());
        $this->assertSame(1000, $events[0]->price->amount());
    }

    public function test_create_product__should_store_name_and_price(): void
    {
        $product = Product::create(
            ProductId::generate(),
            ProductName::fromString('Widget Pro'),
            Money::of(1999, Currency::USD),
        );

        $this->assertSame('Widget Pro', $product->name()->value());
        $this->assertSame(1999, $product->price()->amount());
        $this->assertSame(Currency::USD, $product->price()->currency());
    }

    public function test_pull_domain_events__should_clear_events_after_first_pull(): void
    {
        $product = Product::create(
            ProductId::generate(),
            ProductName::fromString('Widget'),
            Money::of(100, Currency::EUR),
        );
        $product->pullDomainEvents();

        $this->assertEmpty($product->pullDomainEvents());
    }
}
