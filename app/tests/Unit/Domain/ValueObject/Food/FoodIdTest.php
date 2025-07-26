<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Food;

use App\HealthTracker\Domain\ValueObject\Food\FoodId;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class FoodIdTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string|int}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'simple id' => [
                'value' => 1,
            ],
            'common product id' => [
                'value' => 100,
            ],
            'large category id' => [
                'value' => 10000,
            ],
            'string id' => [
                'value' => '42',
            ],
            'string with spaces' => [
                'value' => ' 100 ',
            ],
            'string with comma' => [
                'value' => '1,000',
            ],
            'max mysql int' => [
                'value' => '2147483647', // Maximum value for INT in MySQL
            ],
        ];
    }

    /**
     * @return array<string, array{value: string|int, message: string}>
     */
    public static function invalidValuesProvider(): array
    {
        return [
            'zero id' => [
                'value' => 0,
                'message' => 'Число 0 должно быть больше 0',
            ],
            'negative id' => [
                'value' => -1,
                'message' => 'Число -1 должно быть больше 0',
            ],
            'empty string' => [
                'value' => '',
                'message' => 'Некорректный формат числа: ',
            ],
            'invalid string' => [
                'value' => 'abc',
                'message' => 'Некорректный формат числа: abc',
            ],
            'decimal string' => [
                'value' => '1.5',
                'message' => 'Некорректный формат числа: 1.5',
            ],
            'string with letters' => [
                'value' => '123food',
                'message' => 'Некорректный формат числа: 123food',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $foodId = new FoodId($value);

        if (is_string($value)) {
            $value = (int)str_replace([',',' '], '', trim($value));
        }

        self::assertSame($value, $foodId->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new FoodId($value);
    }

    public function testEquals(): void
    {
        $foodId1 = new FoodId(1);
        $foodId2 = new FoodId('1');
        $foodId3 = new FoodId(2);

        self::assertTrue($foodId1->equals($foodId2));
        self::assertFalse($foodId1->equals($foodId3));
    }

    public function testToString(): void
    {
        $value = 42;
        $foodId = new FoodId($value);

        self::assertSame((string)$value, (string)$foodId);
    }
}
