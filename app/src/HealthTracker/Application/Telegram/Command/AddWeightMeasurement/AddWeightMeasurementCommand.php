<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddWeightMeasurementCommand implements CommandInterface
{
    public function __construct(
        public int $telegramUserId,
        public float|string $weight,
    ) {}
}
