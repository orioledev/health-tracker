<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Shared;

use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

final class StepsAmountTest extends BaseTestCase
{
    /**
     * @return array<string, array{value: string|int}>
     */
    public static function validValuesProvider(): array
    {
        return [
            'small amount' => [
                'value' => 100,
            ],
            'typical daily amount' => [
                'value' => 8000,
            ],
            'high activity amount' => [
                'value' => 15000,
            ],
            'string value' => [
                'value' => '10000',
            ],
            'string with spaces' => [
                'value' => ' 12 000 ',
            ],
            'string with comma' => [
                'value' => '20,000',
            ],
            'string with comma and spaces' => [
                'value' => ' 25,000 ',
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
            'negative value' => [
                'value' => -1000,
                'message' => 'Число -1000 должно быть больше 0',
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
                'value' => '1000.5',
                'message' => 'Некорректный формат числа: 1000.5',
            ],
            'string with letters' => [
                'value' => '1000steps',
                'message' => 'Некорректный формат числа: 1000steps',
            ],
        ];
    }

    #[DataProvider('validValuesProvider')]
    public function testCreateWithValidValue(string|int $value): void
    {
        $stepsAmount = new StepsAmount($value);

        if (is_string($value)) {
            $value = (int)str_replace([',',' '], '', trim($value));
        }

        self::assertSame($value, $stepsAmount->value());
    }

    #[DataProvider('invalidValuesProvider')]
    public function testCreateWithInvalidValueThrowsException(string|int $value, string $message): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage($message);

        new StepsAmount($value);
    }

    public function testEquals(): void
    {
        $stepsAmount1 = new StepsAmount(10000);
        $stepsAmount2 = new StepsAmount('10,000');
        $stepsAmount3 = new StepsAmount(12000);

        self::assertTrue($stepsAmount1->equals($stepsAmount2));
        self::assertFalse($stepsAmount1->equals($stepsAmount3));
    }

    public function testToString(): void
    {
        $stepsAmount = new StepsAmount(10000);

        self::assertSame('10000', (string)$stepsAmount);
    }
}
