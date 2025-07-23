<?php

namespace App\Tests\Unit\Domain\Calculator\UserDailyNorm;

use App\HealthTracker\Domain\Calculator\UserDailyNorm\MacronutrientsDailyNormCalculator;
use App\HealthTracker\Domain\Calculator\UserDailyNorm\MacronutrientsDailyNormCalculatorArgs;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\Tests\Unit\BaseTestCase;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

class MacronutrientsDailyNormCalculatorTest extends BaseTestCase
{
    private MacronutrientsDailyNormCalculator $calculator;

    protected function setUp(): void
    {
        $this->calculator = new MacronutrientsDailyNormCalculator();
    }

    public static function calculateCaloriesForMaleProvider(): array
    {
        return [
            'sedentary_maintain' => [
                80.0, // weight
                180, // height
                30, // age
                ActivityLevel::SEDENTARY,
                WeightTargetType::MAINTENANCE,
                2136 // expected calories
            ],
            'very_active_gain' => [
                70.0,
                175,
                25,
                ActivityLevel::VERY_HIGH,
                WeightTargetType::GAIN,
                3657
            ],
        ];
    }

    public static function calculateCaloriesForFemaleProvider(): array
    {
        return [
            'low_activity_lose' => [
                65.0,
                165,
                28,
                ActivityLevel::LOW,
                WeightTargetType::LOSS,
                1613
            ],
            'middle_maintain' => [
                55.0,
                160,
                35,
                ActivityLevel::MIDDLE,
                WeightTargetType::MAINTENANCE,
                1882
            ],
        ];
    }

    #[Test]
    #[DataProvider('calculateCaloriesForMaleProvider')]
    public function testCalculateForMale(
        float $weightValue,
        int $heightValue,
        int $age,
        ActivityLevel $activityLevel,
        WeightTargetType $weightTargetType,
        int $expectedCalories
    ): void {
        $args = new MacronutrientsDailyNormCalculatorArgs(
            height: new Height($heightValue),
            weight: new Weight($weightValue),
            gender: Gender::MALE,
            age: $age,
            activityLevel: $activityLevel,
            weightTargetType: $weightTargetType
        );

        $result = $this->calculator->calculate($args);

        $this->assertEquals($expectedCalories, $result->calories);
    }

    #[Test]
    #[DataProvider('calculateCaloriesForFemaleProvider')]
    public function testCalculateForFemale(
        float $weightValue,
        int $heightValue,
        int $age,
        ActivityLevel $activityLevel,
        WeightTargetType $weightTargetType,
        int $expectedCalories
    ): void {
        $args = new MacronutrientsDailyNormCalculatorArgs(
            height: new Height($heightValue),
            weight: new Weight($weightValue),
            gender: Gender::FEMALE,
            age: $age,
            activityLevel: $activityLevel,
            weightTargetType: $weightTargetType
        );

        $result = $this->calculator->calculate($args);

        $this->assertEquals($expectedCalories, $result->calories);
    }

    #[Test]
    public function testMacronutrientsCalculation(): void
    {
        $args = new MacronutrientsDailyNormCalculatorArgs(
            height: new Height(175),
            weight: new Weight(70.0),
            gender: Gender::MALE,
            age: 30,
            activityLevel: ActivityLevel::MIDDLE,
            weightTargetType: WeightTargetType::MAINTENANCE
        );

        $result = $this->calculator->calculate($args);

        // Для 2553 калорий:
        // Белки: 2553 * 0.15 / 4 = 95.85г
        // Жиры: 2553 * 0.3 / 9 = 85.1г
        // Углеводы: 2553 * 0.55 / 4 = 351г
        $this->assertEqualsWithDelta(95.85, $result->proteins, 0.2);
        $this->assertEqualsWithDelta(85.1, $result->fats, 0.2);
        $this->assertEqualsWithDelta(351.45, $result->carbohydrates, 0.2);
    }
}
