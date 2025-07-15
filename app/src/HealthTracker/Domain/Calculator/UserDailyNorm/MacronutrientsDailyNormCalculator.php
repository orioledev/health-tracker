<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\UserDailyNorm;

use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;

final readonly class MacronutrientsDailyNormCalculator implements MacronutrientsDailyNormCalculatorInterface
{
    public function calculate(MacronutrientsDailyNormCalculatorArgs $calculatorArgs): Macronutrients
    {
        $caloriesAmount = $this->calculateCaloriesAmount($calculatorArgs);

        return new Macronutrients(
            $caloriesAmount,
            $this->calculateProteinsAmount($caloriesAmount),
            $this->calculateFatsAmount($caloriesAmount),
            $this->calculateCarbohydratesAmount($caloriesAmount)
        );
    }

    private function calculateCaloriesAmount(MacronutrientsDailyNormCalculatorArgs $calculatorArgs): int
    {
        $caloriesAmount = (10 * $calculatorArgs->weight->value())
            + (6.25 * $calculatorArgs->height->value())
            - (5 * $calculatorArgs->age);

        if ($calculatorArgs->gender === Gender::MALE) {
            $caloriesAmount += 5;
        } else {
            $caloriesAmount -= 161;
        }

        $caloriesAmount *= $calculatorArgs->activityLevel->getDailyNormCaloriesAmountCoefficient();
        $caloriesAmount *= $calculatorArgs->weightTargetType->getDailyNormCaloriesAmountCoefficient();

        return (int)round($caloriesAmount);
    }

    private function calculateProteinsAmount(int $caloriesAmount): float
    {
        return $caloriesAmount * 0.15 / 4;
    }

    private function calculateFatsAmount(int $caloriesAmount): float
    {
        return $caloriesAmount * 0.3 / 9;
    }

    private function calculateCarbohydratesAmount(int $caloriesAmount): float
    {
        return $caloriesAmount * 0.55 / 4;
    }
}
