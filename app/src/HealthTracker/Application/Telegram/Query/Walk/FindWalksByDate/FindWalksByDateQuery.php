<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Walk\FindWalksByDate;

use App\Shared\Application\Query\QueryInterface;
use DateTimeInterface;

final readonly class FindWalksByDateQuery implements QueryInterface
{
    public function __construct(
        public int $telegramUserId,
        public DateTimeInterface $date,
    ) {}
}
