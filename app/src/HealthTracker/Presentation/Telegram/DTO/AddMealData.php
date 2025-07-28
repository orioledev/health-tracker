<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\DTO;

use App\HealthTracker\Presentation\Telegram\Handler\MultipleStepHandlerDataInterface;

final class AddMealData implements MultipleStepHandlerDataInterface
{
    public function __construct(
        public string|null $meal = null,
    ) {}

    public function toArray(): array
    {
        return [
            'meal' => $this->meal,
        ];
    }
}
