<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\User;

use App\HealthTracker\Domain\ValueObject\User\UserId;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class UserIdTest extends BaseTestCase
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
            'larger id' => [
                'value' => 1000,
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
                'value' => '123abc',
                'message' => 'Некорректный формат числа: 123abc',
            ],
            'hex string' => [
                'value' => '0x123',
                'message' => 'Некорректный формат числа: 0x123',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $userId = new UserId($value);

        if (is_string($value)) {
            $value = (int)str_replace([',',' '], '', trim($value));
        }

        self::assertSame($value, $userId->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new UserId($value);
    }

    public function testEquals(): void
    {
        $userId1 = new UserId(1);
        $userId2 = new UserId('1');
        $userId3 = new UserId(2);

        self::assertTrue($userId1->equals($userId2));
        self::assertFalse($userId1->equals($userId3));
    }

    public function testToString(): void
    {
        $value = 42;
        $userId = new UserId($value);

        self::assertSame((string)$value, (string)$userId);
    }
}
