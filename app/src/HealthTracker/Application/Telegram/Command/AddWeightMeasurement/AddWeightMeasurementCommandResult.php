<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement;

use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\Shared\Application\Command\CommandInterface;

final readonly class AddWeightMeasurementCommandResult implements CommandInterface
{
    public function __construct(
        public float $currentWeight,
        public float $currentBmi,
        public float $prevWeight,
        public float $initialWeight,
        public float $targetWeight,
        public WeightTargetType $weightTargetType,
    ) {}

    public function toArray(): array
    {
        return [
            'currentWeight' => $this->currentWeight,
            'currentBmi' => $this->currentBmi,
            'prevWeight' => $this->prevWeight,
            'initialWeight' => $this->initialWeight,
            'targetWeight' => $this->targetWeight,
            'weightTargetType' => $this->weightTargetType,
        ];
    }
}
