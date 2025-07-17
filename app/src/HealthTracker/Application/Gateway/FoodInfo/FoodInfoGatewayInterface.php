<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Gateway\FoodInfo;

interface FoodInfoGatewayInterface
{
    public function findOne(FoodInfoGatewayRequest $request): ?FoodInfoGatewayResponse;
}
