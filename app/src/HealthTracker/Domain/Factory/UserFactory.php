<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;

final readonly class UserFactory
{
    public function create(
        int $telegramUserId,
        ?string $telegramUsername,
        string $firstName,
        ?string $lastName,
    ): User
    {
        return new User(
            telegramUserId: new TelegramUserId($telegramUserId),
            telegramUsername: $telegramUsername ? new TelegramUsername($telegramUsername) : null,
            fullName: new FullName($firstName, $lastName),
        );
    }
}
