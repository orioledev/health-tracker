<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserIndicator;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Factory\UserIndicatorFactoryInterface;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

final readonly class CommonUserIndicatorFactory implements UserIndicatorFactoryInterface
{
    public function __construct(
        private BodyMassIndexCalculatorInterface $bodyMassIndexCalculator,
    ) {}

    public function create(
        User $user,
        int $height,
        string|float $initialWeight,
        string|float $targetWeight,
        ActivityLevel $activityLevel,
    ): UserIndicator
    {
        $userIndicator = new UserIndicator(
            user: $user,
            bodyMassIndexCalculator: $this->bodyMassIndexCalculator
        );

        $userIndicator->height = new Height($height);
        $userIndicator->initialWeight = new Weight($initialWeight);
        $userIndicator->targetWeight = new Weight($targetWeight);
        $userIndicator->activityLevel = $activityLevel;

        return $userIndicator;
    }
}
