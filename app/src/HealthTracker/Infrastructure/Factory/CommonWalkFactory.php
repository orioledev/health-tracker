<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorInterface;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Factory\WalkFactoryInterface;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;

final readonly class CommonWalkFactory implements WalkFactoryInterface
{
    public function __construct(
        private WalkCaloriesAmountCalculatorInterface $walkCaloriesAmountCalculator,
    ) {}

    public function create(
        User $user,
        int $steps,
    ): Walk
    {
        return new Walk(
            user: $user,
            steps: new StepsAmount($steps),
            walkCaloriesAmountCalculator: $this->walkCaloriesAmountCalculator
        );
    }
}
