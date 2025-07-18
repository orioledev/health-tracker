<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\Factory\FoodFactoryInterface;
use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\Name;

final readonly class CommonFoodFactory implements FoodFactoryInterface
{
    public function create(
        string $externalId,
        string $name,
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates,
    ): Food
    {
        return new Food(
            externalId: new ExternalId($externalId),
            name: new Name($name),
            macronutrients: new Macronutrients(
                $calories,
                $proteins,
                $fats,
                $carbohydrates
            ),
        );
    }
}
