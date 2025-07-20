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

        $prevWeightMeasurement = $this->weightMeasurementRepository->findPrevWeightMeasurement($weightMeasurement);
        $prevWeight = $prevWeightMeasurement
            ? $prevWeightMeasurement->weight->value()
            : $user->indicator->initialWeight->value();

        return new AddWeightMeasurementCommandResult(
            currentWeight: $weightMeasurement->weight->value(),
            currentBmi: $user->indicator->getCurrentBmi(),
            prevWeight: $prevWeight,
            initialWeight: $user->indicator->initialWeight->value(),
            targetWeight: $user->indicator->targetWeight->value(),
            weightTargetType: $user->indicator->weightTargetType,
        );
    }
}
