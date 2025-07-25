<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use DateTime;

interface WalkRepositoryInterface
{
    public function findById(WalkId $walkId): ?Walk;

    public function getTotalStepsByDate(User $user, DateTime $date): int;

    public function getTotalStepsToday(User $user): int;

    public function save(Walk $walk): void;
}
