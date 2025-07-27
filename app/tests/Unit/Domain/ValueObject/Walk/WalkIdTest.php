<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Walk;

use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class WalkIdTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string|int}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'positive integer' => [
                'value' => 1,
            ],
            'big integer' => [
                'value' => PHP_INT_MAX,
            ],
            'string integer' => [
                'value' => '100',
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
        $walkId = new WalkId($value);

        if (is_string($value)) {
            $value = (int)str_replace([',', ' '], '', trim($value));
        }

        self::assertSame($value, $walkId->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new WalkId($value);
    }

    public function testEquals(): void
    {
        $walkId1 = new WalkId(100);
        $walkId2 = new WalkId('100');
        $walkId3 = new WalkId(200);

        self::assertTrue($walkId1->equals($walkId2));
        self::assertFalse($walkId1->equals($walkId3));
    }
}
