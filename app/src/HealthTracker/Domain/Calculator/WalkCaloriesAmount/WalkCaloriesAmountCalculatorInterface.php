<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\WalkCaloriesAmount;

use App\HealthTracker\Domain\ValueObject\Shared\CaloriesAmount;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;

interface WalkCaloriesAmountCalculatorInterface
{
    public function calculate(
        WalkCaloriesAmountCalculatorArgs $calculatorArgs,
        StepsAmount $steps,
    ): CaloriesAmount;
}
