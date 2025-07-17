<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;

interface MealRepositoryInterface
{
    public function findById(MealId $mealId): ?Meal;

    public function getTotalMacronutrientsToday(User $user): Macronutrients;

    public function save(Meal $meal): void;
}
