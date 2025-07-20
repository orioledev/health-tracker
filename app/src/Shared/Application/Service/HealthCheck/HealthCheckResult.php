<?php

declare(strict_types=1);

namespace App\Shared\Application\Service\HealthCheck;

final class HealthCheckResult
{
    public const STATUS_HEALTHY = 'healthy';
    public const STATUS_UNHEALTHY = 'unhealthy';
    public const STATUS_WARNING = 'warning';
    public const STATUS_CRITICAL = 'critical';

    private string $status;
    private array $checks;
    private \DateTimeInterface $timestamp;
    private ?string $version;

    public function __construct(
        string $status,
        array $checks = [],
        ?\DateTimeInterface $timestamp = null,
        ?string $version = null
    ) {
        $this->status = $status;
        $this->checks = $checks;
        $this->timestamp = $timestamp ?? new \DateTimeImmutable();
        $this->version = $version;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getChecks(): array
    {
        return $this->checks;
    }

    public function getTimestamp(): \DateTimeInterface
    {
        return $this->timestamp;
    }

    public function getVersion(): ?string
    {
        return $this->version;
    }

    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_HEALTHY;
    }

    public function toArray(): array
    {
        $array = [
            'status' => $this->status,
            'timestamp' => $this->timestamp->format(\DateTimeInterface::ATOM),
        ];

        if(!empty($this->checks)) $array['checks'] = $this->checks;
        if(!empty($this->version)) $array['version'] = $this->version;

        return $array;
    }

    public static function healthy(array $checks = [], ?string $version = null): self
    {
        return new self(self::STATUS_HEALTHY, $checks, null, $version);
    }

    public static function unhealthy(array $checks = [], ?string $version = null): self
    {
        return new self(self::STATUS_UNHEALTHY, $checks, null, $version);
    }

    public static function simple(string $status, ?string $message = null): self
    {
        $checks = $message ? ['message' => $message] : [];
        return new self($status, $checks);
    }
}
