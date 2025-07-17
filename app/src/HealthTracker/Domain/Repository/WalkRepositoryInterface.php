<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;

interface WalkRepositoryInterface
{
    public function findById(WalkId $walkId): ?Walk;

    public function getTotalStepsToday(User $user): int;

    public function save(Walk $walk): void;
}
