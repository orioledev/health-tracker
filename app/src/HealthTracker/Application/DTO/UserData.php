<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\DTO;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use DateTimeInterface;

final readonly class UserData
{
    public function __construct(
        public int $id,
        public int $telegramUserId,
        public ?string $telegramUsername,
        public string $firstName,
        public ?string $lastName,
        public DateTimeInterface $birthdate,
        public Gender $gender,
        public int $height,
        public string|float $initialWeight,
        public string|float $targetWeight,
        public ActivityLevel $activityLevel,
        public WeightTargetType $weightTargetType,
        public string|float $initialBmi,
    ) {}

    public static function fromEntity(User $user): self
    {
        return new self(
            id: $user->id->value(),
            telegramUserId: $user->telegramUserId->value(),
            telegramUsername: $user->telegramUsername->value(),
            firstName: $user->fullName->firstName(),
            lastName: $user->fullName->lastName(),
            birthdate: $user->birthdate,
            gender: $user->gender,
            height: $user->indicator->height->value(),
            initialWeight: $user->indicator->initialWeight->value(),
            targetWeight: $user->indicator->targetWeight->value(),
            activityLevel: $user->indicator->activityLevel,
            weightTargetType: $user->indicator->weightTargetType,
            initialBmi: $user->indicator->getInitialBmi(),
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'telegramUserId' => $this->telegramUserId,
            'telegramUsername' => $this->telegramUsername,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'birthdate' => $this->birthdate,
            'gender' => $this->gender,
            'height' => $this->height,
            'initialWeight' => $this->initialWeight,
            'targetWeight' => $this->targetWeight,
            'activityLevel' => $this->activityLevel,
            'weightTargetType' => $this->weightTargetType,
            'initialBmi' => $this->initialBmi,
        ];
    }
}
