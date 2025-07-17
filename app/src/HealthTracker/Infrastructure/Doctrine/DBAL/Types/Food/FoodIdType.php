<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\Food;

use App\HealthTracker\Domain\ValueObject\Food\FoodId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class FoodIdType extends BigIntType
{
    public const string TYPE_NAME = 'food_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?FoodId
    {
        return $value !== null
            ? new FoodId($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof FoodId
            ? $value->value()
            : (int)$value;
    }
}
