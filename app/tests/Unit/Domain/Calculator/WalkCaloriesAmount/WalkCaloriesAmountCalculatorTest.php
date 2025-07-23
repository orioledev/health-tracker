<?php

namespace App\Tests\Unit\Domain\Calculator\WalkCaloriesAmount;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculator;
use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorArgs;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\MockObject\MockObject;

class WalkCaloriesAmountCalculatorTest extends BaseTestCase
{
    private WalkCaloriesAmountCalculator $calculator;
    private MockObject&BodyMassIndexCalculatorInterface $bmiCalculator;

    protected function setUp(): void
    {
        $this->bmiCalculator = $this->createMock(BodyMassIndexCalculatorInterface::class);
        $this->calculator = new WalkCaloriesAmountCalculator($this->bmiCalculator);
    }

    public static function calculateProvider(): array
    {
        return [
            'male_normal_weight' => [
                80.0, // weight
                180, // height
                30, // age
                Gender::MALE,
                25.0, // BMI
                10000, // steps
                487 // expected calories
            ],
            'female_normal_weight' => [
                60.0, // weight
                165, // height
                25, // age
                Gender::FEMALE,
                22.0, // BMI
                8000, // steps
                274 // expected calories
            ],
            'male_overweight' => [
                90.0, // weight
                175, // height
                40, // age
                Gender::MALE,
                29.0, // BMI
                5000, // steps
                254 // expected calories
            ],
            'female_underweight' => [
                45.0, // weight
                170, // height
                20, // age
                Gender::FEMALE,
                18.0, // BMI
                12000, // steps
                333 // expected calories
            ],
        ];
    }

    #[Test]
    #[DataProvider('calculateProvider')]
    public function testCalculate(
        float $weightValue,
        int $heightValue,
        int $age,
        Gender $gender,
        float $bmi,
        int $stepsValue,
        int $expectedCalories
    ): void {
        $weight = new Weight($weightValue);
        $height = new Height($heightValue);
        $steps = new StepsAmount($stepsValue);

        $this->bmiCalculator
            ->expects($this->once())
            ->method('calculate')
            ->with($weight, $height)
            ->willReturn($bmi);

        $args = new WalkCaloriesAmountCalculatorArgs(
            weight: $weight,
            height: $height,
            gender: $gender,
            age: $age
        );

        $result = $this->calculator->calculate($args, $steps);

        $this->assertEquals($expectedCalories, $result->value());
    }
}
