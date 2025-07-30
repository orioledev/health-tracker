<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\DTO;

use App\HealthTracker\Presentation\Telegram\Handler\MultipleStepHandlerDataInterface;

final class AddWalkData implements MultipleStepHandlerDataInterface
{
    public function __construct(
        public string|int|null $steps = null,
    ) {}

    public function toArray(): array
    {
        return [
            'steps' => $this->steps,
        ];
    }
}
