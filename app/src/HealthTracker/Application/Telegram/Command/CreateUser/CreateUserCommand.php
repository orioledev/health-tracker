<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\CreateUser;

use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\Shared\Application\Command\CommandInterface;
use DateTimeInterface;

final readonly class CreateUserCommand implements CommandInterface
{
    public function __construct(
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
    ) {}
}
