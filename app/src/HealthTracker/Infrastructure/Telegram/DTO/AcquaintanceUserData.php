<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\DTO;

use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;
use DateTimeInterface;
use TelegramBot\Api\Types\User;

final class AcquaintanceUserData
{
    public function __construct(
        public ?int $telegramUserId = null,
        public ?string $telegramUsername = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?DateTimeInterface $birthdate = null,
        public ?Gender $gender = null,
        public ?int $height = null,
        public string|float|null $initialWeight = null,
        public string|float|null $targetWeight = null,
        public ?ActivityLevel $activityLevel = null,
        public ?WeightTargetType $weightTargetType = null,
    ) {}

    public function fillTelegramUserData(User $user): void
    {
        $this->telegramUserId = new TelegramUserId($user->getId())->value();
        $this->telegramUsername = new TelegramUsername($user->getUsername())->value();

        $fullName = new FullName($user->getFirstName(), $user->getLastName());
        $this->firstName = $fullName->firstName();
        $this->lastName = $fullName->lastName();
    }

    public function toArray(): array
    {
        return [
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
        ];
    }
}
