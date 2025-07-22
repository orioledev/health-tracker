<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

abstract readonly class AbstractPositiveIntValueObject extends AbstractValueObject
{
    protected const string POSITIVE_INT_PATTERN = '/^[1-9][0-9]*$/';
    protected int $value;

    /**
     * @param int $value
     * @throws InvalidArgumentException
     */
    public function __construct(string|int $value)
    {
        $this->value = $this->normalizeInt($value);
    }

    final public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value() === $other->value();
    }

    protected function normalizeInt(string|int $value): int
    {
        if (is_string($value)) {
            $value = str_replace([',',' '], '', trim($value));
            $this->assertValueIsInt($value);
            $value = (int)$value;
        }

        $this->assertValueIsPositive($value);

        return $value;
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

    /**
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertValueIsInt(string $value): void
    {
        if ($value === '' || !preg_match(static::POSITIVE_INT_PATTERN, $value)) {
            throw new InvalidArgumentException("Некорректный формат числа: $value");
        }
    }


}
