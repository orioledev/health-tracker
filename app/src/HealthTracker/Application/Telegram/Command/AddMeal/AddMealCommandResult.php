<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddMeal;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\Shared\Application\Command\CommandInterface;

final readonly class AddMealCommandResult implements CommandInterface
{
    public function __construct(
        public string $name,
        public int $weight,
        public Macronutrients $currentMacronutrients,
        public Macronutrients $todayMacronutrients,
        public Macronutrients $dailyNormMacronutrients,
    ) {}

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'weight' => $this->weight,
            'currentMacronutrients' => $this->currentMacronutrients->toArray(),
            'todayMacronutrients' => $this->todayMacronutrients->toArray(),
            'dailyNormMacronutrients' => $this->dailyNormMacronutrients->toArray(),
        ];
    }
}
