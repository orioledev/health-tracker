<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\Http\Controller;

use App\Shared\Application\Service\HealthCheck\HealthCheckServiceInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class HealthCheckFullController
{
    private HealthCheckServiceInterface $healthCheckService;

    public function __construct(HealthCheckServiceInterface $healthCheckService)
    {
        $this->healthCheckService = $healthCheckService;
    }
    #[Route("/health", name: "health_check_full", methods: ["GET"])]
    public function check(): JsonResponse
    {
        $result = $this->healthCheckService->performFullCheck();

        $statusCode = $result->isHealthy()
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($result->toArray(), $statusCode);
    }
    #[Route("/health/ping", name: "health_check_ping", methods: ["GET"])]
    public function ping(): JsonResponse
    {
        return new JsonResponse([
            'status' => 'ok',
            'timestamp' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM)
        ]);
    }
    #[Route("/health/ready", name: "health_check_ready", methods: ["GET"])]
    public function ready(): JsonResponse
    {
        $result = $this->healthCheckService->checkReadiness();

        $statusCode = $result->isHealthy()
            ? Response::HTTP_OK
            : Response::HTTP_SERVICE_UNAVAILABLE;

        return new JsonResponse($result->toArray(), $statusCode);
    }
    #[Route("/health/live", name: "health_check_live", methods: ["GET"])]
    public function live(): JsonResponse
    {
        $result = $this->healthCheckService->checkLiveness();

        return new JsonResponse($result->toArray());
    }
}
