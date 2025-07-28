<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Query\Walk\FindWalksByDate;

use App\HealthTracker\Application\DTO\WalkData;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\UserId;
use App\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindWalksByDateQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalkRepositoryInterface $walkRepository,
    ) {}

    public function __invoke(FindWalksByDateQuery $query): FindWalksByDateQueryResult
    {
        $user = $this->userRepository->findByUserIdOrFail(
            new UserId($query->userId)
        );

        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        $walksByDate = $this->walkRepository->findWalksByDate($user, $query->date);

        return new FindWalksByDateQueryResult(
            date: $query->date,
            walks: array_map(
                fn(Walk $walk): WalkData => WalkData::fromEntity($walk),
                $walksByDate
            ),
            daySteps: $this->walkRepository->getTotalStepsByDate($user, $query->date),
            dailyNormSteps: $user->dailyNorm->steps->value(),
        );
    }
}
