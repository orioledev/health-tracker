<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthCheck;

use App\Shared\Domain\HealthCheck\CheckerInterface;
use App\Shared\Domain\HealthCheck\CheckResult;
use Doctrine\DBAL\Connection;

final class DatabaseChecker implements CheckerInterface
{
    private Connection $connection;

    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function getName(): string
    {
        return 'database';
    }

    public function check(): CheckResult
    {
        try {
            $startTime = microtime(true);

            // Простой запрос для проверки соединения
            $this->connection->executeQuery('SELECT 1');

            $responseTime = (microtime(true) - $startTime) * 1000;

            $details = [
                'driver' => $this->connection->getDriver(),
                'database' => $this->connection->getDatabase()
            ];

            return CheckResult::ok(
                'Database connection successful',
                $details,
                $responseTime
            );
        } catch (\Exception $e) {
            return CheckResult::error(
                'Database connection failed',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function isCritical(): bool
    {
        return true;
    }
}
