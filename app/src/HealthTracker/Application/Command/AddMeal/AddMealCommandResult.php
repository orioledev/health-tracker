<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\AddMeal;

use App\HealthTracker\Application\DTO\MealData;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;

final readonly class AddMealCommandResult
{
    public function __construct(
        public MealData $meal,
        public Macronutrients $dayMacronutrients,
        public Macronutrients $dailyNormMacronutrients,
    ) {}

    public function toArray(): array
    {
        return [
            'meal' => $this->meal->toArray(),
            'dayMacronutrients' => $this->dayMacronutrients->toArray(),
            'dailyNormMacronutrients' => $this->dailyNormMacronutrients->toArray(),
        ];
    }
}
