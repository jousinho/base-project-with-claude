<?php

declare(strict_types=1);

namespace App\Product\Application\MessageHandler;

use App\Product\Application\Command\CreateProductCommand;
use App\Product\Application\Service\CreateProductService;
use App\User\Domain\Event\UserWasCreated;

final class UserWasCreatedHandler
{
    public function __construct(
        private readonly CreateProductService $createProductService,
    ) {}

    public function __invoke(UserWasCreated $event): void
    {
        $this->createProductService->execute(new CreateProductCommand(
            name: 'Welcome product for ' . $event->name->value(),
            priceAmount: 100,
            priceCurrency: 'EUR',
        ));
    }
}
