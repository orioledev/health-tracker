<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\User;

use App\Shared\Domain\ValueObject\AbstractValueObject;
use InvalidArgumentException;

final readonly class TelegramUserId extends AbstractValueObject
{
    private int $value;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(int $value)
    {
        $this->assertValueIsNotZero($value);

        $this->value = $value;
    }

    final public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function assertValueIsNotZero(int $value): void
    {
        if ($value === 0) {
            throw new InvalidArgumentException("Число $value не должно быть равно 0");
        }
    }
}
