<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use DateTimeInterface;

interface WalkRepositoryInterface
{
    public function findById(WalkId $walkId): ?Walk;

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @return Walk[]
     */
    public function findWalksByDate(User $user, DateTimeInterface $date): array;

    public function getTotalStepsByDate(User $user, DateTimeInterface $date): int;

    public function getTotalStepsToday(User $user): int;

    public function getDateWithWalks(User $user, DateTimeInterface $date, Direction $direction): ?DateTimeInterface;

    public function save(Walk $walk): void;
}
