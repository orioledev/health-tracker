<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Factory\UserFactoryInterface;
use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;

final readonly class CommonUserFactory implements UserFactoryInterface
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
