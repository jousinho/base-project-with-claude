<?php

declare(strict_types=1);

namespace App\Health\Infrastructure\Http\Controller;

use App\Health\Application\Service\GetHealthStatusService;
use DateTimeInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

final class HealthController
{
    public function __construct(
        private readonly GetHealthStatusService $healthStatus,
    ) {}

    public function __invoke(): JsonResponse
    {
        $status = $this->healthStatus->execute();

        return new JsonResponse([
            'status'    => $status->status(),
            'timestamp' => $status->checkedAt()->format(DateTimeInterface::ATOM),
        ]);
    }
}
