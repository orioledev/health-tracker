<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Enum;

enum Gender: int
{
    case MALE = 1;
    case FEMALE = 2;

    public function getLabel(): string
    {
        return match ($this) {
            self::MALE => 'Мужской',
            self::FEMALE => 'Женский',
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

    public function getStepLengthCoefficient(): float
    {
        return match ($this) {
            self::MALE => 1,
            self::FEMALE => 0.95,
        };
    }
}
