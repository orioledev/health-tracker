<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement\AddWeightMeasurementCommand;
use App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement\AddWeightMeasurementCommandResult;
use App\HealthTracker\Application\Telegram\Query\CheckUserExistenceByTelegramUserId\CheckUserExistenceByTelegramUserIdQuery;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Exception\NeedAcquaintanceException;
use App\HealthTracker\Infrastructure\Telegram\DTO\AddWeightMeasurementData;
use App\HealthTracker\Infrastructure\Telegram\Handler\AddWeightMeasurementHandler;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class AddWeightMeasurementTelegramCommand extends BaseMultipleStepTelegramCommand
{
    public const string NAME = 'Добавить взвешивание';

    public function __construct(
        Environment $twig,
        AddWeightMeasurementHandler $handler,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
    )
    {
        parent::__construct($twig, $handler);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return 'Добавление нового взвешивания';
    }

    public function getSortOrder(): int
    {
        return 200;
    }

    /**
     * @param Update $update
     * @return void
     * @throws NeedAcquaintanceException
     */
    protected function beforeExecute(Update $update): void
    {
        $telegramUser = $this->getTelegramUser($update);

        $isUserExists = $this->queryBus->ask(
            new CheckUserExistenceByTelegramUserIdQuery($telegramUser?->getId())
        );

        if (!$isUserExists) {
            throw new NeedAcquaintanceException();
        }
    }

    /**
     * @param Update $update
     * @return AddWeightMeasurementData
     */
    protected function createData(Update $update): AddWeightMeasurementData
    {
        /** @var AddWeightMeasurementData $data */
        $data = parent::createData($update);

        return $data;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param MultipleStepHandlerDataInterface $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function finalStep(
        BotApi $api,
        Update $update,
        string $chatId,
        MultipleStepHandlerDataInterface $data
    ): void
    {
        $dataClassName = $this->handler->getDataClassName();
        if (!$data instanceof $dataClassName) {
            throw new \InvalidArgumentException('Переданы некорректные данные');
        }

        $telegramUser = $this->getTelegramUser($update);

        if (!$telegramUser) {
            throw new \InvalidArgumentException('Не удалось определить пользователя telegram');
        }

        /** @var AddWeightMeasurementData $data */
        $command = new AddWeightMeasurementCommand(
            telegramUserId: $telegramUser->getId(),
            weight: $data->weight,
        );

        /** @var AddWeightMeasurementCommandResult $result */
        $result = $this->commandBus->dispatch($command);

        $this->sendSuccessMessage($api, $chatId, $result->toArray());
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/add-weight-measurement.html.twig';
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddWeightMeasurementData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step0(BotApi $api, Update $update, string $chatId, AddWeightMeasurementData $data): void
    {
        $this->sendTextMessage($api, $chatId, 'Введи свой текущий вес (кг)');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddWeightMeasurementData $data
     * @return void
     */
    protected function step1(BotApi $api, Update $update, string $chatId, AddWeightMeasurementData $data): void
    {
        $message = $update->getMessage();

        try {
            $weight = new Weight($message->getText());
            $data->weight = $weight->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный вес (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }
    }
}
