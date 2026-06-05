<?php

declare(strict_types=1);

namespace App\Product\Domain\Event;

use App\Product\Domain\ValueObject\Money;
use App\Product\Domain\ValueObject\ProductId;
use App\Product\Domain\ValueObject\ProductName;
use App\Shared\Domain\Event\DomainEvent;

final class ProductWasCreated extends DomainEvent
{
    public function __construct(
        public readonly ProductId $productId,
        public readonly ProductName $name,
        public readonly Money $price,
    ) {
        parent::__construct();
    }
}
