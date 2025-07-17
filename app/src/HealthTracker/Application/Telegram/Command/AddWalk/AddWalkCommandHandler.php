<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWalk;

use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Factory\WalkFactoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class AddWalkCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalkRepositoryInterface $walkRepository,
        private WalkFactoryInterface $walkFactory,
    ) {}

    public function __invoke(AddWalkCommand $command): AddWalkCommandResult
    {
        $user = $this->userRepository->findByTelegramUserIdOrFail(
            new TelegramUserId($command->telegramUserId)
        );

        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        $walk = $this->walkFactory->create(
            user: $user,
            steps: $command->steps
        );

        $this->walkRepository->save($walk);

        return new AddWalkCommandResult(
            currentSteps: $walk->steps->value(),
            currentCalories: $walk->calories->value(),
            todaySteps: $this->walkRepository->getTotalStepsToday($user),
            dailyNormSteps: $user->dailyNorm->steps->value(),
        );
    }
}
