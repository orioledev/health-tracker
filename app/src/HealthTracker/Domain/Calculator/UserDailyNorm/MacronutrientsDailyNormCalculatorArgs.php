<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\UserDailyNorm;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

readonly class MacronutrientsDailyNormCalculatorArgs
{
    public function __construct(
        public Height $height,
        public Weight $weight,
        public Gender $gender,
        public int $age,
        public ActivityLevel $activityLevel,
        public WeightTargetType $weightTargetType,
    ) {}

    /**
     * @param User $user
     * @return self
     * @throws UserInfoNotFilledException
     */
    public static function fromEntity(User $user): self
    {
        if (!$user->isFilled(false)) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        return new self(
            height: $user->indicator->height,
            weight: $user->indicator->currentWeight,
            gender: $user->gender,
            age: $user->getAge(),
            activityLevel: $user->indicator->activityLevel,
            weightTargetType: $user->indicator->weightTargetType,
        );
    }
}
