<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Shared;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class MacronutrientsTest extends BaseTestCase
{
    /**
     * @return array<string, array{
     *     calories: int,
     *     proteins: string|float,
     *     fats: string|float,
     *     carbohydrates: string|float
     * }>
     */
    public static function validValuesProvider(): array
    {
        return [
            'integer values' => [
                'calories' => 100,
                'proteins' => 20,
                'fats' => 30,
                'carbohydrates' => 50,
            ],
            'float values' => [
                'calories' => 150,
                'proteins' => 20.5,
                'fats' => 30.5,
                'carbohydrates' => 50.5,
            ],
            'string decimal values' => [
                'calories' => 200,
                'proteins' => '20.50',
                'fats' => '30.50',
                'carbohydrates' => '50.50',
            ],
            'zero values' => [
                'calories' => 0,
                'proteins' => 0,
                'fats' => 0,
                'carbohydrates' => 0,
            ],
        ];
    }

    /**
     * @return array<string, array{
     *     calories: int,
     *     proteins: string|float,
     *     fats: string|float,
     *     carbohydrates: string|float,
     *     message: string
     * }>
     */
    public static function invalidValuesProvider(): array
    {
        return [
            'negative calories' => [
                'calories' => -100,
                'proteins' => 20,
                'fats' => 30,
                'carbohydrates' => 50,
                'message' => 'Число -100 не может быть отрицательным',
            ],
            'empty string for proteins' => [
                'calories' => 100,
                'proteins' => '',
                'fats' => 30,
                'carbohydrates' => 50,
                'message' => 'Пустая строка не является корректным числом',
            ],
            'invalid string for proteins' => [
                'calories' => 100,
                'proteins' => 'abc',
                'fats' => 30,
                'carbohydrates' => 50,
                'message' => 'Некорректный формат числа: abc',
            ],
            'negative string for fats' => [
                'calories' => 100,
                'proteins' => 20,
                'fats' => '-30',
                'carbohydrates' => 50,
                'message' => 'Некорректный формат числа: -30',
            ],
            'invalid string for carbohydrates' => [
                'calories' => 100,
                'proteins' => 20,
                'fats' => 30,
                'carbohydrates' => '50.abc',
                'message' => 'Некорректный формат числа: 50.abc',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValues(
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates
    ): void {
        $macronutrients = new Macronutrients($calories, $proteins, $fats, $carbohydrates);

        self::assertSame($calories, $macronutrients->calories);
        self::assertEqualsWithDelta((float)$proteins, $macronutrients->proteins, 0.001);
        self::assertEqualsWithDelta((float)$fats, $macronutrients->fats, 0.001);
        self::assertEqualsWithDelta((float)$carbohydrates, $macronutrients->carbohydrates, 0.001);
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValuesThrowsException(
        int $calories,
        string|float $proteins,
        string|float $fats,
        string|float $carbohydrates,
        string $message
    ): void {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Macronutrients($calories, $proteins, $fats, $carbohydrates);
    }

    public function testAdd(): void
    {
        $macronutrients1 = new Macronutrients(100, 20, 30, 50);
        $macronutrients2 = new Macronutrients(150, 25, 35, 55);

        $result = $macronutrients1->add($macronutrients2);

        self::assertSame(250, $result->calories);
        self::assertEqualsWithDelta(45.0, $result->proteins, 0.001);
        self::assertEqualsWithDelta(65.0, $result->fats, 0.001);
        self::assertEqualsWithDelta(105.0, $result->carbohydrates, 0.001);
    }

    public function testSubtractWithValidValues(): void
    {
        $macronutrients1 = new Macronutrients(200, 40, 60, 100);
        $macronutrients2 = new Macronutrients(100, 20, 30, 50);

        $result = $macronutrients1->subtract($macronutrients2);

        self::assertSame(100, $result->calories);
        self::assertEqualsWithDelta(20.0, $result->proteins, 0.001);
        self::assertEqualsWithDelta(30.0, $result->fats, 0.001);
        self::assertEqualsWithDelta(50.0, $result->carbohydrates, 0.001);
    }

    public function testSubtractWithInvalidValuesThrowsException(): void
    {
        $macronutrients1 = new Macronutrients(100, 20, 30, 50);
        $macronutrients2 = new Macronutrients(150, 25, 35, 55);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Результат вычитания не может содержать отрицательные значения');

        $macronutrients1->subtract($macronutrients2);
    }

    public function testMultiplyWithValidFactor(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $factor = 1.5;

        $result = $macronutrients->multiply($factor);

        self::assertSame(150, $result->calories);
        self::assertEqualsWithDelta(30.0, $result->proteins, 0.001);
        self::assertEqualsWithDelta(45.0, $result->fats, 0.001);
        self::assertEqualsWithDelta(75.0, $result->carbohydrates, 0.001);
    }

    public function testMultiplyWithNegativeFactorThrowsException(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $factor = -1.5;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Множитель не может быть отрицательным');

        $macronutrients->multiply($factor);
    }

    public function testPerWeight(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $weightInGrams = 150;

        $result = $macronutrients->perWeight($weightInGrams);

        self::assertSame(150, $result->calories);
        self::assertEqualsWithDelta(30.0, $result->proteins, 0.001);
        self::assertEqualsWithDelta(45.0, $result->fats, 0.001);
        self::assertEqualsWithDelta(75.0, $result->carbohydrates, 0.001);
    }

    public function testChangeCalories(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $newCalories = 200;

        $result = $macronutrients->changeCalories($newCalories);

        self::assertSame($newCalories, $result->calories);
        self::assertSame($macronutrients->proteins, $result->proteins);
        self::assertSame($macronutrients->fats, $result->fats);
        self::assertSame($macronutrients->carbohydrates, $result->carbohydrates);
    }

    public function testChangeProteins(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $newProteins = 25.5;

        $result = $macronutrients->changeProteins($newProteins);

        self::assertSame($macronutrients->calories, $result->calories);
        self::assertEqualsWithDelta($newProteins, $result->proteins, 0.001);
        self::assertSame($macronutrients->fats, $result->fats);
        self::assertSame($macronutrients->carbohydrates, $result->carbohydrates);
    }

    public function testChangeFats(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $newFats = 35.5;

        $result = $macronutrients->changeFats($newFats);

        self::assertSame($macronutrients->calories, $result->calories);
        self::assertSame($macronutrients->proteins, $result->proteins);
        self::assertEqualsWithDelta($newFats, $result->fats, 0.001);
        self::assertSame($macronutrients->carbohydrates, $result->carbohydrates);
    }

    public function testChangeCarbohydrates(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $newCarbohydrates = 55.5;

        $result = $macronutrients->changeCarbohydrates($newCarbohydrates);

        self::assertSame($macronutrients->calories, $result->calories);
        self::assertSame($macronutrients->proteins, $result->proteins);
        self::assertSame($macronutrients->fats, $result->fats);
        self::assertEqualsWithDelta($newCarbohydrates, $result->carbohydrates, 0.001);
    }

    public function testEquals(): void
    {
        $macronutrients1 = new Macronutrients(100, 20, 30, 50);
        $macronutrients2 = new Macronutrients(100, 20.0001, 30.0001, 50.0001);
        $macronutrients3 = new Macronutrients(101, 20, 30, 50);

        self::assertTrue($macronutrients1->equals($macronutrients2));
        self::assertFalse($macronutrients1->equals($macronutrients3));
    }

    public function testJsonSerialize(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $expected = [
            'calories' => 100,
            'proteins' => 20.0,
            'fats' => 30.0,
            'carbohydrates' => 50.0,
        ];

        self::assertSame($expected, $macronutrients->jsonSerialize());
        self::assertSame($expected, $macronutrients->toArray());
    }

    public function testToString(): void
    {
        $macronutrients = new Macronutrients(100, 20, 30, 50);
        $expected = 'Макронутриенты: калории 100, белки 20.0 г, жиры 30.0 г, углеводы 50.0 г)';

        self::assertSame($expected, $macronutrients->toString());
        self::assertSame($expected, (string)$macronutrients);
    }
}
