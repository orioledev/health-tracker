<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\DTO;

use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use DateTimeInterface;

final class AcquaintanceUserData implements MultipleStepHandlerDataInterface
{
    public function __construct(
        public ?DateTimeInterface $birthdate = null,
        public ?Gender $gender = null,
        public ?int $height = null,
        public string|float|null $initialWeight = null,
        public string|float|null $targetWeight = null,
        public ?ActivityLevel $activityLevel = null,
        public ?WeightTargetType $weightTargetType = null,
    ) {}

    public function toArray(): array
    {
        return [
            'birthdate' => $this->birthdate,
            'gender' => $this->gender,
            'height' => $this->height,
            'initialWeight' => $this->initialWeight,
            'targetWeight' => $this->targetWeight,
            'activityLevel' => $this->activityLevel,
            'weightTargetType' => $this->weightTargetType,
        ];
    }
}
