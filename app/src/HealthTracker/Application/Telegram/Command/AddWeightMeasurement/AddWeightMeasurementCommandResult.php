<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddWeightMeasurementCommandResult implements CommandInterface
{
    public function __construct(
        public float $currentWeight,
        public float $initialWeight,
        public float $targetWeight,
    ) {}

    public function toArray(): array
    {
        return [
            'currentWeight' => $this->currentWeight,
            'initialWeight' => $this->initialWeight,
            'targetWeight' => $this->targetWeight,
        ];
    }
}
