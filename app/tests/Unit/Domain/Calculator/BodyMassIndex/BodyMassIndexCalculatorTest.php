<?php

namespace App\Tests\Unit\Domain\Calculator\BodyMassIndex;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculator;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class BodyMassIndexCalculatorTest extends BaseTestCase
{
    private BodyMassIndexCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new BodyMassIndexCalculator();
    }

    public static function calculateProvider(): array
    {
        return [
            'normal weight' => [70.0, 175, 22.9],
            'underweight' => [50.0, 175, 16.3],
            'overweight' => [90.0, 175, 29.4],
            'obese' => [100.0, 175, 32.7],
        ];
    }

    #[Test]
    #[DataProvider('calculateProvider')]
    public function testCalculate(float $weightValue, int $heightValue, float $expectedBmi): void
    {
        $weight = new Weight($weightValue);
        $height = new Height($heightValue);

        $bmi = $this->calculator->calculate($weight, $height);

        $this->assertEqualsWithDelta($expectedBmi, $bmi, 0.1);
    }
}
