<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\Shared;

use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\DecimalType;

final class WeightType extends DecimalType
{
    public const string TYPE_NAME = 'weight';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Weight
    {
        return $value !== null
            ? new Weight($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?float
    {
        return $value?->value();
    }
}
