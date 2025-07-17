<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Parser\MealParser;

final readonly class MealParserRequest
{
    public function __construct(
        public string $input,
    ) {}
}
