<?php

declare(strict_types=1);

namespace App\Tests\Unit\Product\Domain\ValueObject;

use App\Product\Domain\ValueObject\Currency;
use App\Product\Domain\ValueObject\Money;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class MoneyTest extends TestCase
{
    public function test_create_money__should_preserve_amount_and_currency(): void
    {
        $money = Money::of(500, Currency::EUR);

        $this->assertSame(500, $money->amount());
        $this->assertSame(Currency::EUR, $money->currency());
    }

    public function test_create_money__when_zero_amount__should_be_valid(): void
    {
        $money = Money::of(0, Currency::USD);

        $this->assertSame(0, $money->amount());
    }

    public function test_create_money__when_negative_amount__should_throw_exception(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Money amount cannot be negative.');

        Money::of(-1, Currency::EUR);
    }
}
