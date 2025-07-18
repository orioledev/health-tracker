<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserDailyNorm;
use App\HealthTracker\Domain\Factory\UserDailyNormFactoryInterface;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;

final readonly class CommonUserDailyNormFactory implements UserDailyNormFactoryInterface
{
    public function create(
        User $user,
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates,
        int $steps,
    ): UserDailyNorm
    {
        return new UserDailyNorm(
            user: $user,
            macronutrients: new Macronutrients(
                $calories,
                $proteins,
                $fats,
                $carbohydrates
            ),
            steps: new StepsAmount($steps),
        );
    }
}
