<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\WeightMeasurement;

use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class WeightMeasurementIdType extends BigIntType
{
    public const string TYPE_NAME = 'weight_measurement_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WeightMeasurementId
    {
        return $value !== null
            ? new WeightMeasurementId($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof WeightMeasurementId
            ? $value->value()
            : (int)$value;
    }
}
