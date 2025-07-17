<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Calculator\WalkCaloriesAmount;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;

readonly class WalkCaloriesAmountCalculatorArgs
{
    public function __construct(
        public Height $height,
        public Weight $weight,
        public Gender $gender,
        public int $age,
    ) {}

    /**
     * @param User $user
     * @return self
     * @throws UserInfoNotFilledException
     */
    public static function fromEntity(User $user): self
    {
        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        return new self(
            height: $user->indicator->height,
            weight: $user->indicator->currentWeight,
            gender: $user->gender,
            age: $user->getAge(),
        );
    }
}
