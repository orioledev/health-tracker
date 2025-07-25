<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Meal\FindMealsByDate;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use DateTimeInterface;

final readonly class FindMealsByDateQueryResult
{
    public function __construct(
        public DateTimeInterface $date,
        public array $meals,
        public Macronutrients $dayMacronutrients,
        public Macronutrients $dailyNormMacronutrients,
    ) {}

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'meals' => $this->meals,
            'dayMacronutrients' => $this->dayMacronutrients->toArray(),
            'dailyNormMacronutrients' => $this->dailyNormMacronutrients->toArray(),
        ];
    }
}
