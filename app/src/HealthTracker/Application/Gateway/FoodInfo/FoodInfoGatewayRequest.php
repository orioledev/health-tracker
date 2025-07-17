<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Gateway\FoodInfo;

final readonly class FoodInfoGatewayRequest
{
    public function __construct(
        public string $name,
    ) {}
}
