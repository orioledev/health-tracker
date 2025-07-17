<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Factory;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;

interface WalkFactoryInterface
{
    public function create(
        User $user,
        int $steps,
    ): Walk;
}
