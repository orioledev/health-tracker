<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Query\User\FindUserByTelegramUserId;

use App\Shared\Application\Query\QueryInterface;

final readonly class FindUserByTelegramUserIdQuery implements QueryInterface
{
    public function __construct(
        public int $telegramUserId,
    ) {}
}
