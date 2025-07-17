<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserDailyNorm;

interface UserDailyNormFactoryInterface
{
    public function create(
        User $user,
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates,
        int $steps,
    ): UserDailyNorm;
}
