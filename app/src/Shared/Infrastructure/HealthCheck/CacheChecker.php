<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthCheck;

use App\Shared\Domain\HealthCheck\CheckerInterface;
use App\Shared\Domain\HealthCheck\CheckResult;
use Psr\Cache\CacheItemPoolInterface;

final class CacheChecker implements CheckerInterface
{
    private CacheItemPoolInterface $cache;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getName(): string
    {
        return 'cache';
    }

    public function check(): CheckResult
    {
        try {
            $startTime = microtime(true);
            $testKey = 'health_check_' . time();
            $testValue = 'test_value_' . rand(1, 1000);

            // Тест записи в кэш
            $item = $this->cache->getItem($testKey);
            $item->set($testValue);
            $this->cache->save($item);

            // Тест чтения из кэша
            $retrievedItem = $this->cache->getItem($testKey);
            $retrievedValue = $retrievedItem->get();

            // Удаление тестового ключа
            $this->cache->deleteItem($testKey);

            $responseTime = (microtime(true) - $startTime) * 1000;

            if ($retrievedValue === $testValue) {
                return CheckResult::ok(
                    'Cache read/write successful',
                    [],
                    $responseTime
                );
            }

            return CheckResult::error(
                'Cache read/write failed - values do not match'
            );
        } catch (\Exception $e) {
            return CheckResult::error(
                'Cache check failed',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function isCritical(): bool
    {
        return false;
    }
}