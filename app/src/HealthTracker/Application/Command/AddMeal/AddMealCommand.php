<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\AddMeal;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddMealCommand implements CommandInterface
{
    public function __construct(
        public int $userId,
        public string $meal,
    ) {}
}
