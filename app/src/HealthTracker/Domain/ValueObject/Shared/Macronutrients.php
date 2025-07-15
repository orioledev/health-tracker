<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Shared;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JsonSerializable;

#[ORM\Embeddable]
final readonly class Macronutrients implements JsonSerializable
{
    private const float COMPARE_PRECISION = 0.001;
    private const int DECIMAL_SCALE = 2;
    private const string DECIMAL_PATTERN = '/^[0-9]+(\.[0-9]+)?$/';

    #[ORM\Column(name: 'calories', type: Types::SMALLINT)]
    private(set) int $calories;

    #[ORM\Column(name: 'proteins', type: Types::DECIMAL, precision: 6, scale: 2)]
    private(set) float $proteins;

    #[ORM\Column(name: 'fats', type: Types::DECIMAL, precision: 6, scale: 2)]
    private(set) float $fats;

    #[ORM\Column(name: 'carbohydrates', type: Types::DECIMAL, precision: 6, scale: 2)]
    private(set) float $carbohydrates;

    public function __construct(
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates
    )
    {
        $this->assertValueIsNotNegative($calories);

        $this->calories = $calories;
        $this->proteins = $this->normalizeDecimal($proteins);
        $this->fats = $this->normalizeDecimal($fats);
        $this->carbohydrates = $this->normalizeDecimal($carbohydrates);
    }

    public function add(self $other): self
    {
        return new self(
            $this->calories + $other->calories,
            $this->proteins + $other->proteins,
            $this->fats + $other->fats,
            $this->carbohydrates + $other->carbohydrates
        );
    }

    public function subtract(self $other): self
    {
        $newCalories = $this->calories - $other->calories;
        $newProteins = $this->proteins - $other->proteins;
        $newFats = $this->fats - $other->fats;
        $newCarbohydrates = $this->carbohydrates - $other->carbohydrates;

        if ($newCalories < 0 || $newProteins < 0 || $newFats < 0 || $newCarbohydrates < 0) {
            throw new InvalidArgumentException('Результат вычитания не может содержать отрицательные значения');
        }

        return new self(
            $newCalories,
            $newProteins,
            $newFats,
            $newCarbohydrates
        );
    }

    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('Множитель не может быть отрицательным');
        }

        return new self(
            (int)round($this->calories * $factor),
            $this->proteins * $factor,
            $this->fats * $factor,
            $this->carbohydrates * $factor
        );
    }

    public function perWeight(int $weightInGrams): self
    {
        return $this->multiply($weightInGrams / 100);
    }

    public function changeCalories(int $calories): self
    {
        return new self(
            $calories,
            $this->proteins,
            $this->fats,
            $this->carbohydrates
        );
    }

    public function changeProteins(string|float $proteins): self
    {
        return new self(
            $this->calories,
            $proteins,
            $this->fats,
            $this->carbohydrates
        );
    }

    public function changeFats(string|float $fats): self
    {
        return new self(
            $this->calories,
            $this->proteins,
            $fats,
            $this->carbohydrates
        );
    }

    public function changeCarbohydrates(string|float $carbohydrates): self
    {
        return new self(
            $this->calories,
            $this->proteins,
            $this->fats,
            $carbohydrates
        );
    }

    public function equals(self $other): bool
    {
        return $this->calories == $other->calories
            && abs($this->proteins - $other->proteins) < self::COMPARE_PRECISION
            && abs($this->fats - $other->fats) < self::COMPARE_PRECISION
            && abs($this->carbohydrates - $other->carbohydrates) < self::COMPARE_PRECISION;
    }

    public function jsonSerialize(): array
    {
        return [
            'calories' => $this->calories,
            'proteins' => $this->proteins,
            'fats' => $this->fats,
            'carbohydrates' => $this->carbohydrates,
        ];
    }

    public function toArray(): array
    {
        return $this->jsonSerialize();
    }

    public function toString(): string
    {
        return sprintf(
            'Макронутриенты: калории %d, белки %.1f г, жиры %.1f г, углеводы %.1f г)',
            $this->calories,
            $this->proteins,
            $this->fats,
            $this->carbohydrates
        );
    }

    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function normalizeDecimal(string|float $value): float
    {
        if (is_string($value)) {
            if ($value === '') {
                throw new InvalidArgumentException("Пустая строка не является корректным числом");
            }

            $value = str_replace(',', '.', $value);
            $this->assertValueIsDecimal($value);
            $value = (float)$value;
        }

        $this->assertValueIsNotNegative($value);

        return round($value, self::DECIMAL_SCALE);
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function assertValueIsDecimal(string $value): void
    {
        if ($value === '' || !preg_match(self::DECIMAL_PATTERN, $value)) {
            throw new InvalidArgumentException("Некорректный формат числа: $value");
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    protected function assertValueIsNotNegative(int|float $value): void
    {
        if ($value < 0) {
            throw new InvalidArgumentException("Число $value не может быть отрицательным");
        }
    }
}
