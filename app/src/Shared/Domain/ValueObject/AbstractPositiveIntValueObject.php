<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

abstract readonly class AbstractPositiveIntValueObject extends AbstractValueObject
{
    protected int $value;

    /**
     * @param int $value
     * @throws InvalidArgumentException
     */
    public function __construct(int $value)
    {
        $this->assertValueIsPositive($value);

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
     * @param int $value
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertValueIsPositive(int $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Число $value должно быть больше 0");
        }
    }
}
