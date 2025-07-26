<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Walk\FindWalksByDate;

use DateTimeInterface;

final readonly class FindWalksByDateQueryResult
{
    public function __construct(
        public DateTimeInterface $date,
        public array $walks,
        public int $daySteps,
        public int $dailyNormSteps,
    ) {}

    public function toArray(): array
    {
        return [
            'date' => $this->date,
            'walks' => $this->walks,
            'daySteps' => $this->daySteps,
            'dailyNormSteps' => $this->dailyNormSteps,
        ];
    }
}
