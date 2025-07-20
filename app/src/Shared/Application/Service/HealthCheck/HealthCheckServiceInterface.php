<?php

declare(strict_types=1);

namespace App\Shared\Application\Service\HealthCheck;

interface HealthCheckServiceInterface
{
    public function performFullCheck(): HealthCheckResult;
    
    public function checkReadiness(): HealthCheckResult;
    
    public function checkLiveness(): HealthCheckResult;
}
