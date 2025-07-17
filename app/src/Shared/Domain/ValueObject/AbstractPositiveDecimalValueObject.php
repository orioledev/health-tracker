<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

abstract readonly class AbstractPositiveDecimalValueObject extends AbstractValueObject
{
    protected const float COMPARE_PRECISION = 0.001;
    protected const int DECIMAL_SCALE = 2;
    protected const string DECIMAL_PATTERN = '/^[0-9]+(\.[0-9]+)?$/';

    protected float $value;

    /**
     * @param string|float $value
     * @throws InvalidArgumentException
     */
    public function __construct(string|float $value)
    {
        $this->value = $this->normalizeDecimal($value);
    }

    final public function value(): float
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return abs($this->value() - $other->value()) < self::COMPARE_PRECISION;
    }

    protected function normalizeDecimal(string|float $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', $value);
            $this->assertValueIsDecimal($value);
            $value = (float)$value;
        }

        $this->assertValueIsPositive($value);

        return round($value, static::DECIMAL_SCALE);
    }

    /**
     * @param string $value
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertValueIsDecimal(string $value): void
    {
        if ($value === '' || !preg_match(static::DECIMAL_PATTERN, $value)) {
            throw new InvalidArgumentException("Некорректный формат числа: $value");
        }
    }

    /**
     * @param float $value
     * @return void
     * @throws InvalidArgumentException
     */
    protected function assertValueIsPositive(float $value): void
    {
        if ($value <= 0) {
            throw new InvalidArgumentException("Число $value должно быть больше 0");
        }
    }
}
