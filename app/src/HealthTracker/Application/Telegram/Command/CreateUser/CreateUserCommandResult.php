<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\CreateUser;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\Shared\Application\Command\CommandInterface;

final readonly class CreateUserCommandResult implements CommandInterface
{
    public function __construct(
        public Macronutrients $macronutrients,
        public int $steps,
    ) {}

    public function toArray(): array
    {
        return [
            'macronutrients' => $this->macronutrients->toArray(),
            'steps' => $this->steps,
        ];
    }
}
