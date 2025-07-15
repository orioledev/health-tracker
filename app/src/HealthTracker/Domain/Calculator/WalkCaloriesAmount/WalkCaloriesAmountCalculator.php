<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\WalkCaloriesAmount;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\ValueObject\Shared\CaloriesAmount;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

final readonly class WalkCaloriesAmountCalculator implements WalkCaloriesAmountCalculatorInterface
{
    private const float SPEED_COEFFICIENT = 0.7515;
    private BodyMassIndexCalculatorInterface $bodyMassIndexCalculator;

    public function __construct(BodyMassIndexCalculatorInterface $bodyMassIndexCalculator)
    {
        $this->bodyMassIndexCalculator = $bodyMassIndexCalculator;
    }

    public function calculate(
        WalkCaloriesAmountCalculatorArgs $calculatorArgs,
        StepsAmount $stepsAmount,
    ): CaloriesAmount
    {
        $stepLength = $this->calculateStepLength(
            $calculatorArgs->weight,
            $calculatorArgs->height,
            $calculatorArgs->gender,
            $calculatorArgs->age
        );

        $distance = $stepsAmount->value() * $stepLength;

        $caloriesAmount = round((self::SPEED_COEFFICIENT * $calculatorArgs->weight->value() * $distance) / 100_000);

        return new CaloriesAmount((int)$caloriesAmount);
    }

    /**
     * Returns length of the step in centimeters
     *
     * @param Weight $weight
     * @param Height $height
     * @param Gender $gender
     * @param int $age
     * @return int
     */
    private function calculateStepLength(
        Weight $weight,
        Height $height,
        Gender $gender,
        int $age,
    ): int
    {
        $bodyMassIndex = $this->bodyMassIndexCalculator->calculate($weight, $height);

        $stepLength = ($height->value() / 4 + 37) * $gender->getStepLengthCoefficient() - $age / 25 - $bodyMassIndex + 25;

        return (int)round($stepLength);
    }
}
