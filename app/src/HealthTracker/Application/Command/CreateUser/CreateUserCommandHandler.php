<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\CreateUser;

use App\HealthTracker\Application\DTO\UserData;
use App\HealthTracker\Domain\Calculator\UserDailyNorm\MacronutrientsDailyNormCalculatorArgs;
use App\HealthTracker\Domain\Calculator\UserDailyNorm\MacronutrientsDailyNormCalculatorInterface;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserDailyNorm;
use App\HealthTracker\Domain\Entity\UserIndicator;
use App\HealthTracker\Domain\Exception\UserAlreadyExistsException;
use App\HealthTracker\Domain\Factory\UserDailyNormFactoryInterface;
use App\HealthTracker\Domain\Factory\UserFactoryInterface;
use App\HealthTracker\Domain\Factory\UserIndicatorFactoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class CreateUserCommandHandler implements CommandHandlerInterface
{
    private const int DEFAULT_STEPS_AMOUNT = 10_000;

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private UserFactoryInterface $userFactory,
        private UserIndicatorFactoryInterface $userIndicatorFactory,
        private UserDailyNormFactoryInterface $userDailyNormFactory,
        private MacronutrientsDailyNormCalculatorInterface $macronutrientsDailyNormCalculator,
    ) {}

    public function __invoke(CreateUserCommand $command): CreateUserCommandResult
    {
        $user = $this->userRepository->findByTelegramUserId(
            new TelegramUserId($command->telegramUserId)
        );

        if ($user !== null) {
            throw new UserAlreadyExistsException('Пользователь с таким id уже существует');
        }

        // Create user with user indicator
        $user = $this->createUser($command);
        $userIndicator = $this->createUserIndicator($user, $command);

        $user->indicator = $userIndicator;

        // Calculate user daily norms
        $userDailyNorm = $this->createUserDailyNorm($user);
        $user->dailyNorm = $userDailyNorm;

        $this->userRepository->save($user);

        return new CreateUserCommandResult(
            userData: UserData::fromEntity($user),
            macronutrients: $userDailyNorm->macronutrients,
            steps: $user->dailyNorm->steps->value(),
        );
    }

    private function createUser(CreateUserCommand $command): User
    {
        $user = $this->userFactory->create(
            telegramUserId: $command->telegramUserId,
            telegramUsername: $command->telegramUsername,
            firstName: $command->firstName,
            lastName: $command->lastName,
        );

        $user->birthdate = $command->birthdate;
        $user->gender = $command->gender;

        return $user;
    }

    private function createUserIndicator(User $user, CreateUserCommand $command): UserIndicator
    {
        return $this->userIndicatorFactory->create(
            user: $user,
            height: $command->height,
            initialWeight: $command->initialWeight,
            targetWeight: $command->targetWeight,
            activityLevel: $command->activityLevel,
        );
    }

    private function createUserDailyNorm(User $user): UserDailyNorm
    {
        $macronutrients = $this
            ->macronutrientsDailyNormCalculator
            ->calculate(MacronutrientsDailyNormCalculatorArgs::fromEntity($user));

        return $this->userDailyNormFactory->create(
            user: $user,
            calories: $macronutrients->calories,
            proteins: $macronutrients->proteins,
            fats: $macronutrients->fats,
            carbohydrates: $macronutrients->carbohydrates,
            steps: self::DEFAULT_STEPS_AMOUNT
        );
    }
}
