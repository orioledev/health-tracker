<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorInterface;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;

final readonly class WalkFactory
{
    public function __construct(
        private WalkCaloriesAmountCalculatorInterface $walkCaloriesAmountCalculator,
    ) {}

    public function create(
        User $user,
        int $stepsAmount,
    ): Walk
    {
        return new Walk(
            user: $user,
            stepsAmount: new StepsAmount($stepsAmount),
            walkCaloriesAmountCalculator: $this->walkCaloriesAmountCalculator
        );
    }
}
