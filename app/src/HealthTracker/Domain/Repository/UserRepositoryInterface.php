<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\UserId;

interface UserRepositoryInterface
{
    public function findById(UserId $userId): ?User;

    public function findByTelegramUserId(TelegramUserId $telegramUserId): ?User;

    public function save(User $user): void;
}
