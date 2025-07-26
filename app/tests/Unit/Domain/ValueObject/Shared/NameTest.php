<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Shared;

use App\HealthTracker\Domain\ValueObject\Shared\Name;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class NameTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'simple name' => [
                'value' => 'Apple',
            ],
            'compound name' => [
                'value' => 'Green Apple',
            ],
            'name with numbers' => [
                'value' => 'Vitamin B12',
            ],
            'name with special chars' => [
                'value' => 'Mixed Berries & Nuts',
            ],
            'name with dashes' => [
                'value' => 'Sugar-Free Juice',
            ],
            'name with parentheses' => [
                'value' => 'Protein Bar (Chocolate)',
            ],
            'unicode name' => [
                'value' => 'Фрукты',
            ],
            'name with extra spaces' => [
                'value' => '  Trimmed Name  ',
            ],
        ];
    }

    /**
     * @return array<string, array{value: string, message: string}>
     */
    public static function invalidValuesProvider(): array
    {
        $longString = str_repeat('a', 256);

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
                'message' => "Слишком длинная строка [$longString] (максимальное количество символов - 255)",
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string $value): void
    {
        $name = new Name($value);

        self::assertSame(trim($value), $name->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new Name($value);
    }

    public function testEquals(): void
    {
        $name1 = new Name('Apple');
        $name2 = new Name('  Apple  ');  // С пробелами, которые будут обрезаны
        $name3 = new Name('Orange');

        self::assertTrue($name1->equals($name2));
        self::assertFalse($name1->equals($name3));
    }

    public function testToString(): void
    {
        $value = 'Apple';
        $name = new Name($value);

        self::assertSame($value, (string)$name);
    }
}
