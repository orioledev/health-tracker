<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\UserIndicator;

use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SmallIntType;

final class HeightType extends SmallIntType
{
    public const string TYPE_NAME = 'height';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Height
    {
        return $value !== null
            ? new Height($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof Height
            ? $value->value()
            : (int)$value;
    }
}
