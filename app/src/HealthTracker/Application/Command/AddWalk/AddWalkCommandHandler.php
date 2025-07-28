<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Command\AddWalk;

use App\HealthTracker\Application\DTO\WalkData;
use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Factory\WalkFactoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\UserId;
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
        $user = $this->userRepository->findByUserIdOrFail(
            new UserId($command->userId)
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
            walk: WalkData::fromEntity($walk),
            daySteps: $this->walkRepository->getTotalStepsToday($user),
            dailyNormSteps: $user->dailyNorm->steps->value(),
        );
    }
}
