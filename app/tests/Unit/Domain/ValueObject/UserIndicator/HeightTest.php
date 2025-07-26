<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\UserIndicator;

use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class HeightTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string|int}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'average adult male height' => [
                'value' => 175,
            ],
            'average adult female height' => [
                'value' => 165,
            ],
            'tall person height' => [
                'value' => 190,
            ],
            'short person height' => [
                'value' => 150,
            ],
            'string height' => [
                'value' => '180',
            ],
            'string with spaces' => [
                'value' => ' 170 ',
            ],
            'string with comma' => [
                'value' => '1,85',
            ],
        ];
    }

    /**
     * @return array<string, array{value: string|int, message: string}>
     */
    public static function invalidValuesProvider(): array
    {
        return [
            'zero height' => [
                'value' => 0,
                'message' => 'Число 0 должно быть больше 0',
            ],
            'negative height' => [
                'value' => -170,
                'message' => 'Число -170 должно быть больше 0',
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
                'value' => '170.5',
                'message' => 'Некорректный формат числа: 170.5',
            ],
            'string with letters' => [
                'value' => '170cm',
                'message' => 'Некорректный формат числа: 170cm',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $height = new Height($value);

        if (is_string($value)) {
            $value = (int)str_replace([',',' '], '', trim($value));
        }

        self::assertSame($value, $height->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Height($value);
    }

    public function testEquals(): void
    {
        $height1 = new Height(175);
        $height2 = new Height('175');
        $height3 = new Height(180);

        self::assertTrue($height1->equals($height2));
        self::assertFalse($height1->equals($height3));
    }

    public function testToString(): void
    {
        $height = new Height(175);

        self::assertSame('175', (string)$height);
    }
}
