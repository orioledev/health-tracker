<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Meal\FindMealsByDate;

use App\Shared\Application\Query\QueryInterface;
use DateTimeInterface;

final readonly class FindMealsByDateQuery implements QueryInterface
{
    public function __construct(
        public int $telegramUserId,
        public DateTimeInterface $date,
    ) {}
}
