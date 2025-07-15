<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use InvalidArgumentException;

abstract readonly class AbstractStringValueObject extends AbstractValueObject
{
    protected const int MIN_LENGTH = 1;
    protected const int MAX_LENGTH = 255;
    protected string $value;

    /**
     * @throws InvalidArgumentException
     */
    public function __construct(string $value)
    {
        $value = trim($value);

        $this->assertValueIsNotEmpty($value);
        $this->assertValueLengthIsGte($value, static::MIN_LENGTH);
        $this->assertValueLengthIsLte($value, static::MAX_LENGTH);

        $this->value = $value;
    }

    final public function value(): string
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
    protected function assertValueIsNotEmpty(string $value): void
    {
        if (empty($value)) {
            throw new InvalidArgumentException('Строка не должна быть пустой');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function assertValueLengthIsGte(string $value, int $min): void
    {
        $length = mb_strlen($value);

        if ($length < $min) {
            throw new InvalidArgumentException("Слишком короткая строка [$value] (минимальное количество символов - $min)");
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function assertValueLengthIsLte(string $value, int $max): void
    {
        $length = mb_strlen($value);

        if ($length > $max) {
            throw new InvalidArgumentException("Слишком длинная строка [$value] (максимальное количество символов - $max)");
        }
    }
}
