<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Meal;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;

final readonly class MealId extends AbstractPositiveIntValueObject {}
