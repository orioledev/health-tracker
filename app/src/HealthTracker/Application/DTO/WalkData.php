<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\DTO;

use App\HealthTracker\Domain\Entity\Walk;
use DateTimeImmutable;

final readonly class WalkData
{
    public function __construct(
        public int $steps,
        public int $calories,
        public DateTimeImmutable $createdAt,
    ) {}

    public static function fromEntity(Walk $walk): self
    {
        return new self(
            steps: $walk->steps->value(),
            calories: $walk->calories->value(),
            createdAt: $walk->createdAt,
        );
    }

    public function toArray(): array
    {
        return [
            'steps' => $this->steps,
            'calories' => $this->calories,
            'createdAt' => $this->createdAt,
        ];
    }
}
