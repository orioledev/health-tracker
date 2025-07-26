<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\WeightMeasurement;

use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class WeightMeasurementIdTest extends BaseTestCase
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
            'daily measurement id' => [
                'value' => 365,  // например, для ежедневных измерений в течение года
            ],
            'large measurement set' => [
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
                'value' => '123kg',
                'message' => 'Некорректный формат числа: 123kg',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $measurementId = new WeightMeasurementId($value);

        if (is_string($value)) {
            $value = (int)str_replace([',',' '], '', trim($value));
        }

        self::assertSame($value, $measurementId->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new WeightMeasurementId($value);
    }

    public function testEquals(): void
    {
        $measurementId1 = new WeightMeasurementId(1);
        $measurementId2 = new WeightMeasurementId('1');
        $measurementId3 = new WeightMeasurementId(2);

        self::assertTrue($measurementId1->equals($measurementId2));
        self::assertFalse($measurementId1->equals($measurementId3));
    }

    public function testToString(): void
    {
        $value = 42;
        $measurementId = new WeightMeasurementId($value);

        self::assertSame((string)$value, (string)$measurementId);
    }

    public function testSequentialMeasurements(): void
    {
        // Тестируем последовательные ID измерений
        $measurements = [
            new WeightMeasurementId(1),
            new WeightMeasurementId(2),
            new WeightMeasurementId(3)
        ];

        // Проверяем, что каждый следующий ID больше предыдущего
        for ($i = 0; $i < count($measurements) - 1; $i++) {
            self::assertGreaterThan(
                $measurements[$i]->value(),
                $measurements[$i + 1]->value(),
                sprintf(
                    'Ожидалось, что ID %d будет меньше ID %d',
                    $measurements[$i]->value(),
                    $measurements[$i + 1]->value()
                )
            );
        }
    }
}
