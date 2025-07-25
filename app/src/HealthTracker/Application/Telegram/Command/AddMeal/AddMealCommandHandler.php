<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddMeal;

use App\HealthTracker\Application\DTO\MealData;
use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayInterface;
use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayRequest;
use App\HealthTracker\Application\Gateway\FoodInfo\FoodInfoGatewayResponse;
use App\HealthTracker\Application\Parser\MealParser\MealParserInterface;
use App\HealthTracker\Application\Parser\MealParser\MealParserRequest;
use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\Exception\FoodNotFoundException;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Factory\FoodFactoryInterface;
use App\HealthTracker\Domain\Factory\MealFactoryInterface;
use App\HealthTracker\Domain\Repository\FoodRepositoryInterface;
use App\HealthTracker\Domain\Repository\MealRepositoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\HealthTracker\Domain\ValueObject\Shared\Name;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class AddMealCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private FoodRepositoryInterface $foodRepository,
        private MealRepositoryInterface $mealRepository,
        private MealParserInterface $mealParser,
        private FoodInfoGatewayInterface $foodInfoGateway,
        private FoodFactoryInterface $foodFactory,
        private MealFactoryInterface $mealFactory,
    ) {}

    public function __invoke(AddMealCommand $command): AddMealCommandResult
    {
        $user = $this->userRepository->findByTelegramUserIdOrFail(
            new TelegramUserId($command->telegramUserId)
        );

        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        $mealParserRequest = new MealParserRequest($command->meal);
        $parsedMeal = $this->mealParser->parse($mealParserRequest);

        $food = $this->foodRepository->findByName(new Name($parsedMeal->name));

        // If food is not found in our storage, we find it through the gateway
        if (!$food) {
            $foodInfoGatewayResponse = $this->findFoodViaGateway($parsedMeal->name);
            $food = $this->createNewFood($foodInfoGatewayResponse);
        }

        $meal = $this->mealFactory->create(
            user: $user,
            food: $food,
            name: $parsedMeal->name,
            weight: $parsedMeal->weight,
        );

        $this->mealRepository->save($meal);

        return new AddMealCommandResult(
            meal: MealData::fromEntity($meal),
            dayMacronutrients: $this->mealRepository->getTotalMacronutrientsToday($user),
            dailyNormMacronutrients: $user->dailyNorm->macronutrients,
        );
    }

    private function findFoodViaGateway(string $name): FoodInfoGatewayResponse
    {
        $request = new FoodInfoGatewayRequest($name);
        $response = $this->foodInfoGateway->findOne($request);

        if ($response === null) {
            throw new FoodNotFoundException('Продукт с таким названием не найден');
        }

        return $response;
    }

    private function createNewFood(FoodInfoGatewayResponse $response): Food
    {
        $food = $this->foodRepository->findByExternalId(new ExternalId($response->externalId));

        if ($food === null) {
            $food = $this->foodFactory->create(
                externalId: $response->externalId,
                name: $response->externalName,
                calories: $response->calories,
                proteins: $response->proteins,
                fats: $response->fats,
                carbohydrates: $response->carbohydrates,
            );

            $this->foodRepository->save($food);
        }

        return $food;
    }
}
