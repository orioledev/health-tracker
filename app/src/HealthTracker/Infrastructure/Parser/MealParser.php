<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Parser;

use App\HealthTracker\Application\Parser\MealParser\Exception\MealParserException;
use App\HealthTracker\Application\Parser\MealParser\MealParserInterface;
use App\HealthTracker\Application\Parser\MealParser\MealParserRequest;
use App\HealthTracker\Application\Parser\MealParser\MealParserResponse;

final readonly class MealParser implements MealParserInterface
{
    public function parse(MealParserRequest $request): MealParserResponse
    {
        $input = trim($request->input);

        if (empty($input)) {
            throw new MealParserException('Введите название продукта и его вес');
        }

        $pattern = '/(\d+(?:\.\d+)?)\s*(граммов|грамм|гр\.|гр|г\.|г)?/im';

        if (!preg_match($pattern, $input, $matches)) {
            throw new MealParserException('Не удалось определить вес блюда');
        }

        $weight = (int)$matches[1];

        if ($weight <= 0) {
            throw new MealParserException('Вес блюда должен быть положительным числом');
        }

        $name = preg_replace($pattern, '', $input);
        $name = preg_replace('/\s+/', ' ', $name);
        $name = trim($name, ' .,;:-');

        if (empty($name)) {
            throw new MealParserException('Не удалось определить название блюда');
        }

        return new MealParserResponse($name, $weight);
    }
}
