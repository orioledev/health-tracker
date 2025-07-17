<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Parser\MealParser;

interface MealParserInterface
{
    public function parse(MealParserRequest $request): MealParserResponse;
}
