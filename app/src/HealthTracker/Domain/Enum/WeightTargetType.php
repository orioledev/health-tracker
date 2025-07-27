<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Enum;

enum WeightTargetType: int
{
    case LOSS = 1;
    case MAINTENANCE = 2;
    case GAIN = 3;

    public function getLabel(): string
    {
        return match ($this) {
            self::LOSS => 'Снижение веса',
            self::MAINTENANCE => 'Поддержание веса',
            self::GAIN => 'Набор веса',
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
            self::LOSS => 0.85,
            self::MAINTENANCE => 1,
            self::GAIN => 1.15,
        };
    }

    public static function getWeightTargetTypeByWeightDiff(float $diff): self
    {
        if ($diff < 0) {
            return self::LOSS;
        } elseif ($diff > 0) {
            return self::GAIN;
        }

        return self::MAINTENANCE;
    }
}
