<?php

declare(strict_types=1);

namespace App\Tests\Unit\Health\Domain\ValueObject;

use App\Health\Domain\ValueObject\HealthStatus;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class HealthStatusTest extends TestCase
{
    public function test_create_health_status__should_have_ok_status(): void
    {
        $status = HealthStatus::create();

        $this->assertSame('ok', $status->status());
    }

    public function test_create_health_status__should_have_recent_timestamp(): void
    {
        $before = new DateTimeImmutable();
        $status = HealthStatus::create();
        $after = new DateTimeImmutable();

        $this->assertGreaterThanOrEqual($before->getTimestamp(), $status->checkedAt()->getTimestamp());
        $this->assertLessThanOrEqual($after->getTimestamp(), $status->checkedAt()->getTimestamp());
    }
}
