<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\AddWeightMeasurement;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddWeightMeasurementCommand implements CommandInterface
{
    public function __construct(
        public int $userId,
        public float|string $weight,
    ) {}
}
