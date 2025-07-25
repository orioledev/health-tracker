<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Meal\FindMealsByDate;

use App\HealthTracker\Application\DTO\MealData;
use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Repository\MealRepositoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindMealsByDateQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MealRepositoryInterface $mealRepository,
    ) {}

    public function __invoke(FindMealsByDateQuery $query): FindMealsByDateQueryResult
    {
        $user = $this->userRepository->findByTelegramUserIdOrFail(
            new TelegramUserId($query->telegramUserId)
        );

        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        $mealsByDate = $this->mealRepository->findMealsByDate($user, $query->date);

        return new FindMealsByDateQueryResult(
            date: $query->date,
            meals: array_map(
                fn(Meal $meal): MealData => MealData::fromEntity($meal),
                $mealsByDate
            ),
            dayMacronutrients: $this->mealRepository->getTotalMacronutrientsByDate($user, $query->date),
            dailyNormMacronutrients: $user->dailyNorm->macronutrients,
        );
    }
}
