<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;

interface UserFactoryInterface
{
    public function create(
        int $telegramUserId,
        ?string $telegramUsername,
        string $firstName,
        ?string $lastName,
    ): User;
}
