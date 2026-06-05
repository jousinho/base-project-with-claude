<?php

declare(strict_types=1);

namespace App\Product\Domain\ValueObject;

enum Currency: string
{
    case EUR = 'EUR';
    case USD = 'USD';
}
