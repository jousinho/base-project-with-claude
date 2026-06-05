<?php

declare(strict_types=1);

namespace App\Health\Application\Service;

use App\Health\Domain\ValueObject\HealthStatus;

final class GetHealthStatusService
{
    public function execute(): HealthStatus
    {
        return HealthStatus::create();
    }
}
