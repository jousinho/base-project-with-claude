<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Domain\ValueObject;

use App\Product\Domain\ValueObject\ProductName;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class ProductNameTest extends TestCase
{
    public function test_create_product_name__should_preserve_value(): void
    {
        $name = ProductName::fromString('Widget Pro');

        $this->assertSame('Widget Pro', $name->value());
    }

    public function test_create_product_name__when_empty__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ProductName cannot be empty.');

        ProductName::fromString('');
    }

    public function test_create_product_name__when_200_chars__should_be_valid(): void
    {
        $name = ProductName::fromString(str_repeat('a', 200));

        $this->assertSame(200, strlen($name->value()));
    }

    public function test_create_product_name__when_201_chars__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('ProductName cannot exceed 200 characters.');

        ProductName::fromString(str_repeat('a', 201));
    }
}
