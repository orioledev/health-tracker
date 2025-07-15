<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Enum;

enum ActivityLevel: int
{
    case SEDENTARY = 1;
    case LOW = 2;
    case MIDDLE = 3;
    case HIGH = 4;
    case VERY_HIGH = 5;

    public function getLabel(): string
    {
        return match ($this) {
            self::SEDENTARY => 'Сидячий образ жизни',
            self::LOW => 'Легкая активность (упражнения 1-3 раза в неделю)',
            self::MIDDLE => 'Умеренная активность (упражнения 3-5 раз в неделю)',
            self::HIGH => 'Высокая активность (упражнения 6-7 раз в неделю)',
            self::VERY_HIGH => 'Очень высокая активность (упражнения каждый день или физическая работа)',
        };
    }

    public static function getLabelByValue(int $value): ?string
    {
        $gender = self::tryFrom($value);
        return $gender?->getLabel();
    }

    public static function getList(): array
    {
        $list = [];
        foreach (self::cases() as $case) {
            $list[$case->value] = $case->getLabel();
        }
        return $list;
    }

    public function getDailyNormCaloriesAmountCoefficient(): float
    {
        return match ($this) {
            self::SEDENTARY => 1.2,
            self::LOW => 1.375,
            self::MIDDLE => 1.55,
            self::HIGH => 1.725,
            self::VERY_HIGH => 1.9,
        };
    }
}
