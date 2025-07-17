<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddMeal;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddMealCommand implements CommandInterface
{
    public function __construct(
        public int $telegramUserId,
        public string $meal,
    ) {}
}
