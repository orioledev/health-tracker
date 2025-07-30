<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\AddWalk;

use App\HealthTracker\Application\DTO\WalkData;

final readonly class AddWalkCommandResult
{
    public function __construct(
        public WalkData $walk,
        public int $daySteps,
        public int $dailyNormSteps,
    ) {}

    public function toArray(): array
    {
        return [
            'walk' => $this->walk,
            'daySteps' => $this->daySteps,
            'dailyNormSteps' => $this->dailyNormSteps,
        ];
    }
}
