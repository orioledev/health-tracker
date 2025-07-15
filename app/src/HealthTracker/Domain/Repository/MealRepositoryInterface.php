<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\ValueObject\Meal\MealId;

interface MealRepositoryInterface
{
    public function findById(MealId $mealId): ?Meal;

    public function save(Meal $meal): void;
}
