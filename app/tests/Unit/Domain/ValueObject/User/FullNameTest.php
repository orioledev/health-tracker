<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\User;

use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class FullNameTest extends BaseTestCase
{
    /**
     * @return array<string, array{firstName: string, lastName: ?string, expected: string}>
     */
    public static function fullNameProvider(): array
    {
        return [
            'first name only' => [
                'firstName' => 'John',
                'lastName' => null,
                'expected' => 'John',
            ],
            'first and last name' => [
                'firstName' => 'John',
                'lastName' => 'Doe',
                'expected' => 'John Doe',
            ],
            'complex first name' => [
                'firstName' => 'Jean-Pierre',
                'lastName' => 'Doe',
                'expected' => 'Jean-Pierre Doe',
            ],
            'complex last name' => [
                'firstName' => 'John',
                'lastName' => 'van der Berg',
                'expected' => 'John van der Berg',
            ],
            'cyrillic name' => [
                'firstName' => 'Иван',
                'lastName' => 'Петров',
                'expected' => 'Иван Петров',
            ],
        ];
    }

    #[DataProvider('fullNameProvider')]
    public function testCreateFullName(string $firstName, ?string $lastName, string $expected): void
    {
        $fullName = new FullName($firstName, $lastName);
        self::assertSame($expected, $fullName->value());
        self::assertSame($firstName, $fullName->firstName());
        self::assertSame($lastName, $fullName->lastName());
    }
}
