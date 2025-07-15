<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\DBAL\Types\User;

use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

final class TelegramUsernameType extends StringType
{
    public const string TYPE_NAME = 'telegram_username_type';

    public function getName(): string
    {
        return self::TYPE_NAME;
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?TelegramUsername
    {
        return $value !== null
            ? new TelegramUsername($value)
            : null;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        return $value?->value();
    }
}
