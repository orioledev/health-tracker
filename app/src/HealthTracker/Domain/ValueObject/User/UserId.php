<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\User;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;

final readonly class UserId extends AbstractPositiveIntValueObject {}
