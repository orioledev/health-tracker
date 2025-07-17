<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\Food;

interface FoodFactoryInterface
{
    public function create(
        string $externalId,
        string $name,
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates,
    ): Food;
}
