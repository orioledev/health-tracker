<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWalk;

use App\Shared\Application\Command\CommandInterface;

final readonly class AddWalkCommand implements CommandInterface
{
    public function __construct(
        public int $telegramUserId,
        public int $steps,
    ) {}
}
