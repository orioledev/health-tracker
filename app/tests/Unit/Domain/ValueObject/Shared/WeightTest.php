<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\ValueObject\Shared;

use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\Tests\Unit\BaseTestCase;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;

class WeightTest extends BaseTestCase
{
    public static function validWeightProvider(): array
    {
        return [
            'minimal valid weight' => ['weight' => 0.01],
            'integer weight' => ['weight' => 50],
            'large weight' => ['weight' => 999.99],
        ];
    }

    public static function invalidWeightProvider(): array
    {
        return [
            'empty string' => ['weight' => ''],
            'zero' => ['weight' => 0],
            'negative integer' => ['weight' => -50],
            'negative float' => ['weight' => -50.5],
            'non numeric string' => ['weight' => 'abc'],
            'string with spaces' => ['weight' => ' 50.5 '],
        ];
    }

    public static function formatVariantsProvider(): array
    {
        return [
            'integer as string' => ['input' => '50', 'expected' => 50.0],
            'float as string with dot' => ['input' => '50.5', 'expected' => 50.5],
            'float as string with comma' => ['input' => '50,5', 'expected' => 50.5],
            'integer as number' => ['input' => 50, 'expected' => 50.0],
            'float as number' => ['input' => 50.55, 'expected' => 50.55],
        ];
    }

    #[DataProvider('validWeightProvider')]
    public function testCreateWithValidWeight(string|int|float $weight): void
    {
        $weight = new Weight($weight);
        self::assertInstanceOf(Weight::class, $weight);
    }

    #[DataProvider('invalidWeightProvider')]
    public function testCreateWithInvalidWeightShouldThrowException(string|int|float $weight): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Weight($weight);
    }

    #[DataProvider('formatVariantsProvider')]
    public function testWeightValueMethodShouldReturnNormalizedValue(string|int|float $input, float $expected): void
    {
        $weight = new Weight($input);
        self::assertEqualsWithDelta($expected, $weight->value(), 0.001);
    }

    public function testWeightEqualityWithSameValue(): void
    {
        $weight1 = new Weight(50.5);
        $weight2 = new Weight('50.5');
        $weight3 = new Weight('50,5');

        self::assertTrue($weight1->equals($weight2));
        self::assertTrue($weight2->equals($weight3));
        self::assertTrue($weight1->equals($weight3));
    }

    public function testWeightEqualityWithDifferentValue(): void
    {
        $weight1 = new Weight(50.5);
        $weight2 = new Weight(50.6);

        self::assertFalse($weight1->equals($weight2));
    }

    public function testWeightEqualityWithinPrecision(): void
    {
        // Тест на сравнение чисел в пределах допустимой погрешности
        $weight1 = new Weight(50.5001);
        $weight2 = new Weight(50.5002);

        self::assertTrue($weight1->equals($weight2));
    }

    public function testWeightEqualityOutsidePrecision(): void
    {
        // Тест на сравнение чисел за пределами допустимой погрешности
        $weight1 = new Weight(50.5);
        $weight2 = new Weight(50.6);

        self::assertFalse($weight1->equals($weight2));
    }
}

