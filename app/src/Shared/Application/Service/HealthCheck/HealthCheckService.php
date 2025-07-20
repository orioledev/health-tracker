<?php

declare(strict_types=1);

namespace App\Shared\Application\Service\HealthCheck;

use App\Shared\Domain\HealthCheck\CheckerInterface;
use Symfony\Component\DependencyInjection\Attribute\TaggedIterator;

final class HealthCheckService implements HealthCheckServiceInterface
{
    /** @var CheckerInterface[] */


    public function __construct(
        private iterable $checkers,
        private string $version = 'unknown'
    )
    {
    }

    public function performFullCheck(): HealthCheckResult
    {
        $checks = [];
        $overallStatus = HealthCheckResult::STATUS_HEALTHY;

        foreach ($this->checkers as $checker) {
            $checkResult = $checker->check();
            $checks[$checker->getName()] = $checkResult->toArray();

            // Определяем общий статус
            if (!$checkResult->isHealthy()) {
                if ($checker->isCritical() || $checkResult->getStatus() === 'critical') {
                    $overallStatus = HealthCheckResult::STATUS_UNHEALTHY;
                } elseif ($overallStatus === HealthCheckResult::STATUS_HEALTHY) {
                    $overallStatus = HealthCheckResult::STATUS_WARNING;
                }
            }
        }

        return new HealthCheckResult(
            $overallStatus,
            $checks,
            null,
            $this->version
        );
    }

    public function checkReadiness(): HealthCheckResult
    {
        // Для readiness проверяем только критичные компоненты
        $checkersArray = iterator_to_array($this->checkers);
        $criticalCheckers = array_filter($checkersArray, fn($checker) => $checker->isCritical());

        foreach ($criticalCheckers as $checker) {
            $result = $checker->check();
            if (!$result->isHealthy()) {
                return HealthCheckResult::simple(
                    HealthCheckResult::STATUS_UNHEALTHY,
                    sprintf('%s is not ready: %s', $checker->getName(), $result->getMessage())
                );
            }
        }

        return HealthCheckResult::simple(HealthCheckResult::STATUS_HEALTHY, 'All critical services are ready');
    }

    public function checkLiveness(): HealthCheckResult
    {
        // Liveness - просто проверяем что приложение живо
        return HealthCheckResult::simple(
            HealthCheckResult::STATUS_HEALTHY,
            'Application is alive'
        );
    }
}
