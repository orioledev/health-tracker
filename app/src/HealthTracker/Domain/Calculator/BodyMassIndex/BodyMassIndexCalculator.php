<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\BodyMassIndex;

use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

final readonly class BodyMassIndexCalculator implements BodyMassIndexCalculatorInterface
{
    public function calculate(Weight $weight, Height $height): float
    {
        return round($weight->value() / pow($height->value() / 100, 2), 1);
    }
}
