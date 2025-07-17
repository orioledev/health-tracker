<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\WeightMeasurement;

interface WeightMeasurementFactoryInterface
{
    public function create(
        User $user,
        string|float $weight,
    ): WeightMeasurement;
}
