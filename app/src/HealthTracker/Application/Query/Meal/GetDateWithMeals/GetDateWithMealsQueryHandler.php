<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Query\Meal\GetDateWithMeals;

use App\HealthTracker\Domain\Repository\MealRepositoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\UserId;
use App\Shared\Application\Query\QueryHandlerInterface;
use DateTimeInterface;

final readonly class GetDateWithMealsQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MealRepositoryInterface $mealRepository,
    ) {}

    public function __invoke(GetDateWithMealsQuery $query): ?DateTimeInterface
    {
        $user = $this->userRepository->findByUserIdOrFail(
            new UserId($query->userId)
        );

        return $this->mealRepository->getDateWithMeals($user, $query->date, $query->direction);
    }
}
