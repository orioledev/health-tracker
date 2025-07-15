<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserIndicator;

final readonly class UserIndicatorFactory
{
    public function __construct(
        private BodyMassIndexCalculatorInterface $bodyMassIndexCalculator,
    ) {}

    public function create(User $user): UserIndicator
    {
        return new UserIndicator(
            user: $user,
            bodyMassIndexCalculator: $this->bodyMassIndexCalculator
        );
    }
}
