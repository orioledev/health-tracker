<?php

declare(strict_types=1);

namespace App\Shared\Domain\HealthCheck;

interface CheckerInterface
{
    public function getName(): string;
    
    public function check(): CheckResult;
    
    public function isCritical(): bool;
}

final class CheckResult
{
    public const STATUS_OK = 'healthy';
    public const STATUS_WARNING = 'warning';
    public const STATUS_ERROR = 'unhealthy';
    public const STATUS_CRITICAL = 'critical';

    private string $status;
    private string $message;
    private array $details;
    private ?float $responseTime;

    public function __construct(
        string $status,
        string $message,
        array $details = [],
        ?float $responseTime = null
    ) {
        $this->status = $status;
        $this->message = $message;
        $this->details = $details;
        $this->responseTime = $responseTime;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getDetails(): array
    {
        return $this->details;
    }

    public function getResponseTime(): ?float
    {
        return $this->responseTime;
    }

    public function isHealthy(): bool
    {
        return $this->status === self::STATUS_OK;
    }

    public function toArray(): array
    {
        $result = [
            'status' => $this->status,
            'message' => $this->message,
        ];

        if (!empty($this->details)) {
            $result['details'] = $this->details;
        }

        if ($this->responseTime !== null) {
            $result['response_time_ms'] = round($this->responseTime, 2);
        }

        return $result;
    }

    public static function ok(string $message, array $details = [], ?float $responseTime = null): self
    {
        return new self(self::STATUS_OK, $message, $details, $responseTime);
    }

    public static function warning(string $message, array $details = []): self
    {
        return new self(self::STATUS_WARNING, $message, $details);
    }

    public static function error(string $message, array $details = []): self
    {
        return new self(self::STATUS_ERROR, $message, $details);
    }

    public static function critical(string $message, array $details = []): self
    {
        return new self(self::STATUS_CRITICAL, $message, $details);
    }
}