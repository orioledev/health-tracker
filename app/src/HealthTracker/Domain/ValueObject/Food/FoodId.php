<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Food;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;

final readonly class FoodId extends AbstractPositiveIntValueObject {}
