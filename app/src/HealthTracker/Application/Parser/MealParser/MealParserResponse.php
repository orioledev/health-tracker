<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Parser\MealParser;

final readonly class MealParserResponse
{
    public function __construct(
        public string $name,
        public int $weight,
    ) {}
}
