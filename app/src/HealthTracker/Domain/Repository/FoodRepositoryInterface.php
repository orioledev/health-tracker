<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\HealthTracker\Domain\ValueObject\Food\FoodId;
use App\HealthTracker\Domain\ValueObject\Shared\Name;

interface FoodRepositoryInterface
{
    public function findById(FoodId $foodId): ?Food;

    public function findByExternalId(ExternalId $externalId): ?Food;

    public function findByName(Name $name): ?Food;

    public function save(Food $food): void;
}
