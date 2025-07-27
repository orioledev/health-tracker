<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Meal;

use App\HealthTracker\Domain\ValueObject\Meal\Weight;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class WeightTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string|int}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'small weight' => [
                'value' => 5,
            ],
            'medium weight' => [
                'value' => 100,
            ],
            'large weight' => [
                'value' => 1000,
            ],
            'string integer' => [
                'value' => '150',
            ],
            'string with spaces' => [
                'value' => ' 200 ',
            ],
            'string with comma' => [
                'value' => '1,000',
            ],
            'string with comma and spaces' => [
                'value' => ' 1,500 ',
            ],
        ];
    }

    /**
     * @return array<string, array{value: string|int, message: string}>
     */
    public static function invalidValuesProvider(): array
    {
        return [
            'zero' => [
                'value' => 0,
                'message' => 'Число 0 должно быть больше 0',
            ],
            'negative integer' => [
                'value' => -100,
                'message' => 'Число -100 должно быть больше 0',
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
                'value' => '100.5',
                'message' => 'Некорректный формат числа: 100.5',
            ],
            'string with letters' => [
                'value' => '100abc',
                'message' => 'Некорректный формат числа: 100abc',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $weight = new Weight($value);

        if (is_string($value)) {
            $value = (int)str_replace([',', ' '], '', trim($value));
        }

        self::assertSame($value, $weight->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Weight($value);
    }

    public function testEquals(): void
    {
        $weight1 = new Weight(100);
        $weight2 = new Weight('100');
        $weight3 = new Weight(200);

        self::assertTrue($weight1->equals($weight2));
        self::assertFalse($weight1->equals($weight3));
    }
}
