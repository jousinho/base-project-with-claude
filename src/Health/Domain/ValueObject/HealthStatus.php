<?php

declare(strict_types=1);

namespace App\Health\Domain\ValueObject;

use DateTimeImmutable;

final class HealthStatus
{
    private function __construct(
        private readonly string $status,
        private readonly DateTimeImmutable $checkedAt,
    ) {}

    public static function create(): self
    {
        return new self('ok', new DateTimeImmutable());
    }

    public function status(): string
    {
        return $this->status;
    }

    public function checkedAt(): DateTimeImmutable
    {
        return $this->checkedAt;
    }
}
