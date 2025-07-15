<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\UserIndicator;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;

final readonly class Height extends AbstractPositiveIntValueObject
{
    protected int $value;
}
