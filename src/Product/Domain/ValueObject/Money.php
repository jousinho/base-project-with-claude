<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

use InvalidArgumentException;

final class Money
{
    private function __construct(
        private readonly int $amount,
        private readonly Currency $currency,
    ) {}

    public static function of(int $amount, Currency $currency): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Money amount cannot be negative.');
        }

        return new self($amount, $currency);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function currency(): Currency
    {
        return $this->currency;
    }
}
