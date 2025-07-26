<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserIndicator;
use App\HealthTracker\Domain\Enum\ActivityLevel;

interface UserIndicatorFactoryInterface
{
    public function create(
        User $user,
        int $height,
        string|float $initialWeight,
        string|float $targetWeight,
        ActivityLevel $activityLevel,
    ): UserIndicator;
}
