<?php

declare(strict_types=1);

namespace App\Shared\Infrastructure\HealthCheck;

use App\Shared\Domain\HealthCheck\CheckerInterface;
use App\Shared\Domain\HealthCheck\CheckResult;

final class FilesystemChecker implements CheckerInterface
{
    private string $projectDir;

    public function __construct(string $projectDir)
    {
        $this->projectDir = $projectDir;
    }

    public function getName(): string
    {
        return 'filesystem';
    }

    public function check(): CheckResult
    {
        try {
            $varDir = $this->projectDir . '/var/';
            $testFile = $varDir . 'health_check_test.tmp';

            // Проверяем существование директории
            if (!is_dir($varDir)) {
                return CheckResult::error(
                    'Var directory does not exist',
                    ['directory' => $varDir]
                );
            }

            // Тест записи
            $testContent = 'health_check_' . time();
            $bytesWritten = file_put_contents($testFile, $testContent);

            if ($bytesWritten === false) {
                return CheckResult::error(
                    'Cannot write to filesystem',
                    ['directory' => $varDir]
                );
            }

            // Тест чтения
            $readContent = file_get_contents($testFile);
            
            // Удаление тестового файла
            @unlink($testFile);

            if ($readContent === $testContent) {
                return CheckResult::ok(
                    'Filesystem read/write successful',
                    ['writable_directory' => $varDir]
                );
            }

            return CheckResult::error(
                'Filesystem read/write failed - content mismatch'
            );
        } catch (\Exception $e) {
            return CheckResult::error(
                'Filesystem check failed',
                ['error' => $e->getMessage()]
            );
        }
    }

    public function isCritical(): bool
    {
        return false;
    }
}