<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use DateTimeInterface;

interface MealRepositoryInterface
{
    public function findById(MealId $mealId): ?Meal;

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @return Meal[]
     */
    public function findMealsByDate(User $user, DateTimeInterface $date): array;

    public function getTotalMacronutrientsByDate(User $user, DateTimeInterface $date): Macronutrients;

    public function getTotalMacronutrientsToday(User $user): Macronutrients;

    public function getDateWithMeals(User $user, DateTimeInterface $date, Direction $direction): ?DateTimeInterface;

    public function save(Meal $meal): void;
}
