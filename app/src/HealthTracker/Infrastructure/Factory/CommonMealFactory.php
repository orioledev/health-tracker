<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Factory\MealFactoryInterface;
use App\HealthTracker\Domain\ValueObject\Meal\Weight;
use App\HealthTracker\Domain\ValueObject\Shared\Name;

final readonly class CommonMealFactory implements MealFactoryInterface
{
    public function create(
        User $user,
        Food $food,
        string $name,
        int $weight,
    ): Meal
    {
        return new Meal(
            user: $user,
            food: $food,
            name: new Name($name),
            weight: new Weight($weight),
        );
    }
}
