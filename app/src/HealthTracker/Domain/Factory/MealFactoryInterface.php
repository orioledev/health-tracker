<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;

interface MealFactoryInterface
{
    public function create(
        User $user,
        Food $food,
        string $name,
        int $weight,
    ): Meal;
}
