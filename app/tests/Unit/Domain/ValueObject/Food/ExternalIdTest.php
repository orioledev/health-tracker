<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Food;

use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class ExternalIdTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'numeric id' => [
                'value' => '12345',
            ],
            'alphanumeric id' => [
                'value' => 'ABC123',
            ],
            'uuid style' => [
                'value' => '550e8400-e29b-41d4-a716-446655440000',
            ],
            'barcode style' => [
                'value' => '4607012073689',
            ],
            'with special chars' => [
                'value' => 'PROD_123-456',
            ],
            'with underscores' => [
                'value' => 'external_food_id_123',
            ],
            'with dots' => [
                'value' => 'food.catalog.123',
            ],
            'mixed case' => [
                'value' => 'FoodDB-123abc',
            ],
            'trimmed spaces' => [
                'value' => ' ABC123 ',
            ],
        ];
    }

    /**
     * @return array<string, array{value: string, message: string}>
     */
    public static function invalidValuesProvider(): array
    {
        $longString = str_repeat('a', 65);

        return [
            'empty string' => [
                'value' => '',
                'message' => 'Строка не должна быть пустой',
            ],
            'whitespace only' => [
                'value' => '   ',
                'message' => 'Строка не должна быть пустой',
            ],
            'too long' => [
                'value' => $longString,
                'message' => "Слишком длинная строка [$longString] (максимальное количество символов - 64)",
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string $value): void
    {
        $externalId = new ExternalId($value);

        self::assertSame(trim($value), $externalId->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new ExternalId($value);
    }

    public function testEquals(): void
    {
        $externalId1 = new ExternalId('ABC123');
        $externalId2 = new ExternalId(' ABC123 '); // С пробелами, которые будут обрезаны
        $externalId3 = new ExternalId('DEF456');

        self::assertTrue($externalId1->equals($externalId2));
        self::assertFalse($externalId1->equals($externalId3));
    }

    public function testToString(): void
    {
        $value = 'ABC123';
        $externalId = new ExternalId($value);

        self::assertSame($value, (string)$externalId);
    }

    public function testLengthLimits(): void
    {
        // Тест минимальной длины (1 символ)
        $minLength = new ExternalId('A');
        self::assertSame('A', $minLength->value());

        // Тест максимальной длины (64 символа)
        $maxLengthString = str_repeat('A', 64);
        $maxLength = new ExternalId($maxLengthString);
        self::assertSame($maxLengthString, $maxLength->value());
    }
}
