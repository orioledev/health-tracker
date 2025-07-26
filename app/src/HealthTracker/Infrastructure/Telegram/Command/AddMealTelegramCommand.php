<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Command\AddMeal\AddMealCommand;
use App\HealthTracker\Application\Telegram\Command\AddMeal\AddMealCommandResult;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Exception\NeedAcquaintanceException;
use App\HealthTracker\Infrastructure\Telegram\DTO\AddMealData;
use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Infrastructure\Telegram\Handler\AddMealHandler;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class AddMealTelegramCommand extends BaseMultipleStepTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        AddMealHandler $handler,
        private readonly CommandBusInterface $commandBus,
    )
    {
        parent::__construct($twig, $queryBus, $handler);
    }

    public function getName(): string
    {
        return TelegramCommand::ADD_MEAL->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::ADD_MEAL->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::ADD_MEAL->getDescription();
    }

    public function getSortOrder(): int
    {
        return 300;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws NeedAcquaintanceException
     */
    protected function beforeExecute(BotApi $api, Update $update): void
    {
        if (!$this->isUserExists) {
            throw new NeedAcquaintanceException();
        }
    }

    /**
     * @param Update $update
     * @return AddMealData
     */
    protected function createData(Update $update): AddMealData
    {
        /** @var AddMealData $data */
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

        /** @var AddMealData $data */
        $command = new AddMealCommand(
            telegramUserId: $this->telegramUser->getId(),
            meal: $data->meal,
        );

        /** @var AddMealCommandResult $result */
        $result = $this->commandBus->dispatch($command);

        $this->sendSuccessMessage($api, $chatId, $result->toArray());
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/add-meal.html.twig';
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddMealData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step0(BotApi $api, Update $update, string $chatId, AddMealData $data): void
    {
        $this->sendTextMessage($api, $chatId, 'Напиши, что ты съел (например, "Говядина вареная: 150 г")');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddMealData $data
     * @return void
     */
    protected function step1(BotApi $api, Update $update, string $chatId, AddMealData $data): void
    {
        $meal = trim((string)$update->getMessage()?->getText());

        if (empty($meal)) {
            throw new InvalidParameterException('Ты ничего не написал');
        }

        $data->meal = $meal;
    }
}
