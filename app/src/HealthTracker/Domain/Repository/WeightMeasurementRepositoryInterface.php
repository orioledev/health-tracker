<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\WeightMeasurement;
use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;

interface WeightMeasurementRepositoryInterface
{
    public function findById(WeightMeasurementId $weightMeasurementId): ?WeightMeasurement;

    public function findPrevWeightMeasurement(WeightMeasurement $weightMeasurement): ?WeightMeasurement;

    public function save(WeightMeasurement $weightMeasurement): void;
}
