<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\UserDailyNorm;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;

interface MacronutrientsDailyNormCalculatorInterface
{
    public function calculate(MacronutrientsDailyNormCalculatorArgs $calculatorArgs): Macronutrients;
}
