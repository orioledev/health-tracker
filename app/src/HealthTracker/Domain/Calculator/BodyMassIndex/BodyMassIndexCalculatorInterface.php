<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\BodyMassIndex;

use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

interface BodyMassIndexCalculatorInterface
{
    public function calculate(Weight $weight, Height $height): float;
}
