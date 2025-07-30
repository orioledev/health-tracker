<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\DTO;

use App\HealthTracker\Presentation\Telegram\Handler\MultipleStepHandlerDataInterface;

final class AddWeightMeasurementData implements MultipleStepHandlerDataInterface
{
    public function __construct(
        public string|float|null $weight = null,
    ) {}

    public function toArray(): array
    {
        return [
            'weight' => $this->weight,
        ];
    }
}
