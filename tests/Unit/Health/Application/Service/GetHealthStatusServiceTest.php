<?php

declare(strict_types=1);

namespace App\Tests\Unit\Health\Application\Service;

use App\Health\Application\Service\GetHealthStatusService;
use App\Health\Domain\ValueObject\HealthStatus;
use PHPUnit\Framework\TestCase;

final class GetHealthStatusServiceTest extends TestCase
{
    public function test_get_health_status__should_return_health_status_with_ok_status(): void
    {
        $service = new GetHealthStatusService();

        $result = $service->execute();

        $this->assertInstanceOf(HealthStatus::class, $result);
        $this->assertSame('ok', $result->status());
    }
}
