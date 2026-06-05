<?php

declare(strict_types=1);

namespace App\Tests\Unit\Health\Infrastructure\Http\Controller;

use App\Health\Application\Service\GetHealthStatusService;
use App\Health\Infrastructure\Http\Controller\HealthController;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function test_health_check__should_return_200_with_ok_status(): void
    {
        $controller = new HealthController(new GetHealthStatusService());

        $response = $controller->__invoke();
        $data = json_decode($response->getContent(), true);

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $data['status']);
        $this->assertArrayHasKey('timestamp', $data);
    }
}
