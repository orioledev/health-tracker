<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\WeightMeasurement;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;

final readonly class WeightMeasurementId extends AbstractPositiveIntValueObject {}
