<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\Walk;

use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class WalkIdType extends BigIntType
{
    public const string TYPE_NAME = 'walk_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WalkId
    {
        return $value !== null
            ? new WalkId($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof WalkId
            ? $value->value()
            : (int)$value;
    }
}
