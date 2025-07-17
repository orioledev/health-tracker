<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\Meal;

use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class MealIdType extends BigIntType
{
    public const string TYPE_NAME = 'meal_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?MealId
    {
        return $value !== null
            ? new MealId($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof MealId
            ? $value->value()
            : (int)$value;
    }
}
