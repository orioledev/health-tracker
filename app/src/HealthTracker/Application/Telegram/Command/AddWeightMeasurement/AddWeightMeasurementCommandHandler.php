<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement;

use App\HealthTracker\Domain\Exception\UserInfoNotFilledException;
use App\HealthTracker\Domain\Factory\WeightMeasurementFactoryInterface;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\Repository\WeightMeasurementRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Command\CommandHandlerInterface;

final readonly class AddWeightMeasurementCommandHandler implements CommandHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WeightMeasurementRepositoryInterface $weightMeasurementRepository,
        private WeightMeasurementFactoryInterface $weightMeasurementFactory,
    ) {}

    public function __invoke(AddWeightMeasurementCommand $command): AddWeightMeasurementCommandResult
    {
        $user = $this->userRepository->findByTelegramUserIdOrFail(
            new TelegramUserId($command->telegramUserId)
        );

        if (!$user->isFilled()) {
            throw new UserInfoNotFilledException('Не заполнены данные пользователя');
        }

        $weightMeasurement = $this->weightMeasurementFactory->create(
            user: $user,
            weight: $command->weight
        );

        $this->weightMeasurementRepository->save($weightMeasurement);

        return new AddWeightMeasurementCommandResult(
            currentWeight: $command->weight,
            initialWeight: $user->indicator->initialWeight->value(),
            targetWeight: $user->indicator->targetWeight->value(),
        );
    }
}
