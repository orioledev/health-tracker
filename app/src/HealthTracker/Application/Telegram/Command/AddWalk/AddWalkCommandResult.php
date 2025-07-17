<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWalk;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddWalkCommandResult implements CommandInterface
{
    public function __construct(
        public int $currentSteps,
        public int $currentCalories,
        public int $todaySteps,
        public int $dailyNormSteps,
    ) {}

    public function toArray(): array
    {
        return [
            'currentSteps' => $this->currentSteps,
            'currentCalories' => $this->currentCalories,
            'todaySteps' => $this->todaySteps,
            'dailyNormSteps' => $this->dailyNormSteps,
        ];
    }
}
