<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\User;

use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\BigIntType;

final class TelegramUserIdType extends BigIntType
{
    public const string TYPE_NAME = 'telegram_user_id';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TelegramUserId
    {
        return $value !== null
            ? new TelegramUserId($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        return $value instanceof TelegramUserId
            ? $value->value()
            : (int)$value;
    }
}
