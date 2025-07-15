<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Shared;

use App\Shared\Domain\ValueObject\AbstractPositiveDecimalValueObject;

final readonly class Weight extends AbstractPositiveDecimalValueObject {}
