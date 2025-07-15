<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\WeightMeasurement;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;

final readonly class WeightMeasurementFactory
{
    public function create(
        User $user,
        string|float $weight,
    ): WeightMeasurement
    {
        return new WeightMeasurement(
            user: $user,
            weight: new Weight($weight),
        );
    }
}
