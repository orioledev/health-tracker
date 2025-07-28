<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\CreateUser;

use App\HealthTracker\Application\DTO\UserData;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;

final readonly class CreateUserCommandResult
{
    public function __construct(
        public UserData $userData,
        public Macronutrients $macronutrients,
        public int $steps,
    ) {}

    public function toArray(): array
    {
        return [
            'userData' => $this->userData->toArray(),
            'macronutrients' => $this->macronutrients->toArray(),
            'steps' => $this->steps,
        ];
    }
}
