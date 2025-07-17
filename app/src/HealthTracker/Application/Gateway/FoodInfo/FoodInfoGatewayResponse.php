<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Gateway\FoodInfo;

final readonly class FoodInfoGatewayResponse
{
    public function __construct(
        public string $externalId,
        public string $externalName,
        public int $calories,
        public float $proteins,
        public float $fats,
        public float $carbohydrates,
    ) {}
}
