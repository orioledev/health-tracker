<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\User;

use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class TelegramUsernameTest extends BaseTestCase
{
    /**
     * @return array<string, array{string}>
     */
    public static function validUsernamesProvider(): array
    {
        return [
            'simple username' => ['username'=>'johndoe'],
            'username with underscore' => ['username'=>'john_doe'],
            'username with numbers' => ['username'=>'john123'],
            'maximum length username' => ['username'=>'j' . str_repeat('o', 253) . 'n'], // Max длина 255
        ];
    }

    /**
     * @return array<string, array{string}>
     */
    public static function invalidUsernamesProvider(): array
    {
        return [
            'empty string' => ['username'=>''], // Пустое значение
            'only spaces' => ['username'=>' '], // Только пробелы
            'too long string' => ['username'=>str_repeat('a', 256)], // Превышение максимальной длины
        ];
    }

    #[DataProvider('validUsernamesProvider')]
    public function testCreateWithValidUsername(string $username): void
    {
        $username = new TelegramUsername($username);

        self::assertInstanceOf(TelegramUsername::class, $username);
    }

    #[DataProvider('invalidUsernamesProvider')]
    public function testCreateWithInvalidUsernameShouldThrowException(string $username): void
    {
        $this->expectException(InvalidArgumentException::class);

        new TelegramUsername($username);
    }

    public function testUsernameValueMethodShouldReturnOriginalTrimmedValue(): void
    {
        $originalUsername = '  test_user  ';
        $username = new TelegramUsername($originalUsername);

        self::assertEquals('test_user', $username->value());
    }

    public function testUserNameEqualityWithSameValue(): void
    {
        $username1 = new TelegramUsername('test_user');
        $username2 = new TelegramUsername('test_user');

        self::assertTrue($username1->equals($username2));
    }

    public function testUserNameEqualityWithDifferentValue(): void
    {
        $username1 = new TelegramUsername('test_user1');
        $username2 = new TelegramUsername('test_user2');

        self::assertFalse($username1->equals($username2));
    }
}
