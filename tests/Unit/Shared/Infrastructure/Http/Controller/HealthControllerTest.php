<?php

declare(strict_types=1);

namespace App\Tests\Unit\Shared\Infrastructure\Http\Controller;

use App\Shared\Infrastructure\Http\Controller\HealthController;
use PHPUnit\Framework\TestCase;

final class HealthControllerTest extends TestCase
{
    public function test_health_check__should_return_200_with_ok_status(): void
    {
        $controller = new HealthController();
        $response = $controller->__invoke();

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['status' => 'ok'], json_decode($response->getContent(), true));
    }
}
