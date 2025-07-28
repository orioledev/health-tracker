<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Query\Walk\GetDateWithWalks;

use App\HealthTracker\Domain\Enum\Direction;
use App\Shared\Application\Query\QueryInterface;
use DateTimeInterface;

final readonly class GetDateWithWalksQuery implements QueryInterface
{
    public function __construct(
        public int $userId,
        public DateTimeInterface $date,
        public Direction $direction,
    ) {}
}
