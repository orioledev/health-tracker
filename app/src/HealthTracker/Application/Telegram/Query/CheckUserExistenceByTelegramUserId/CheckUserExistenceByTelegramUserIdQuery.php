<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\CheckUserExistenceByTelegramUserId;

use App\Shared\Application\Query\QueryInterface;

final readonly class CheckUserExistenceByTelegramUserIdQuery implements QueryInterface
{
    public function __construct(
        public int $telegramUserId,
    ) {}
}
