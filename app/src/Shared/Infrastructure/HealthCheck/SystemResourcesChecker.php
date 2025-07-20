<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthCheck;

use App\Shared\Domain\HealthCheck\CheckerInterface;
use App\Shared\Domain\HealthCheck\CheckResult;

final class SystemResourcesChecker implements CheckerInterface
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getName(): string
    {
        return 'system_resources';
    }

    public function check(): CheckResult
    {
        $checks = [
            'memory' => $this->checkMemory(),
            'disk' => $this->checkDiskSpace()
        ];

        $overallStatus = CheckResult::STATUS_OK;
        $messages = [];

        foreach ($checks as $name => $check) {
            if ($check['status'] === CheckResult::STATUS_CRITICAL) {
                $overallStatus = CheckResult::STATUS_CRITICAL;
                $messages[] = sprintf('%s: %s', $name, $check['message']);
            } elseif ($check['status'] === CheckResult::STATUS_WARNING && $overallStatus === CheckResult::STATUS_OK) {
                $overallStatus = CheckResult::STATUS_WARNING;
                $messages[] = sprintf('%s: %s', $name, $check['message']);
            }
        }

        $message = empty($messages) ? 'System resources are healthy' : implode('; ', $messages);

        return new CheckResult($overallStatus, $message, $checks);
    }

    public function isCritical(): bool
    {
        return false;
    }

    private function checkMemory(): array
    {
        $memoryUsage = memory_get_usage(true);
        $memoryLimit = $this->parseMemoryLimit(ini_get('memory_limit'));

        $usagePercentage = $memoryLimit > 0 ? ($memoryUsage / $memoryLimit) * 100 : 0;

        $status = CheckResult::STATUS_OK;
        $message = 'Memory usage normal';

        if ($usagePercentage > 90) {
            $status = CheckResult::STATUS_CRITICAL;
            $message = 'Memory usage critical';
        } elseif ($usagePercentage > 80) {
            $status = CheckResult::STATUS_WARNING;
            $message = 'Memory usage high';
        }

        return [
            'status' => $status,
            'message' => $message,
            'current_usage' => $this->formatBytes($memoryUsage),
            'memory_limit' => ini_get('memory_limit'),
            'usage_percentage' => round($usagePercentage, 2)
        ];
    }

    private function checkDiskSpace(): array
    {
        try {
            $freeBytes = (int)disk_free_space($this->projectDir);
            $totalBytes = (int)disk_total_space($this->projectDir);

            if ($freeBytes === false || $totalBytes === false) {
                return [
                    'status' => CheckResult::STATUS_ERROR,
                    'message' => 'Cannot determine disk space'
                ];
            }

            $usagePercentage = (($totalBytes - $freeBytes) / $totalBytes) * 100;

            $status = CheckResult::STATUS_OK;
            $message = 'Disk space sufficient';

            if ($usagePercentage > 95) {
                $status = CheckResult::STATUS_CRITICAL;
                $message = 'Disk space critical';
            } elseif ($usagePercentage > 85) {
                $status = CheckResult::STATUS_WARNING;
                $message = 'Disk space low';
            }

            return [
                'status' => $status,
                'message' => $message,
                'free_space' => $this->formatBytes($freeBytes),
                'total_space' => $this->formatBytes($totalBytes),
                'usage_percentage' => round($usagePercentage, 2)
            ];
        } catch (\Exception $e) {
            return [
                'status' => CheckResult::STATUS_ERROR,
                'message' => 'Disk space check failed',
                'error' => $e->getMessage()
            ];
        }
    }

    private function parseMemoryLimit(string $memoryLimit): int
    {
        if ($memoryLimit === '-1') {
            return 0; // Unlimited
        }

        $unit = strtolower(substr($memoryLimit, -1));
        $value = (int) $memoryLimit;

        switch ($unit) {
            case 'g':
                return $value * 1024 * 1024 * 1024;
            case 'm':
                return $value * 1024 * 1024;
            case 'k':
                return $value * 1024;
            default:
                return $value;
        }
    }

    private function formatBytes(int $size, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $size > 1024 && $i < count($units) - 1; $i++) {
            $size /= 1024;
        }

        return round($size, $precision) . ' ' . $units[$i];
    }
}
