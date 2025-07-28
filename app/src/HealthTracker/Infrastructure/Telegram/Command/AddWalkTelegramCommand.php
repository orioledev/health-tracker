<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Command\AddWalk\AddWalkCommand;
use App\HealthTracker\Application\Command\AddWalk\AddWalkCommandResult;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Exception\NeedAcquaintanceException;
use App\HealthTracker\Infrastructure\Telegram\DTO\AddWalkData;
use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Infrastructure\Telegram\Handler\AddWalkHandler;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class AddWalkTelegramCommand extends BaseMultipleStepTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        AddWalkHandler $handler,
        private readonly CommandBusInterface $commandBus,
    )
    {
        parent::__construct($twig, $queryBus, $handler);
    }

    public function getName(): string
    {
        return TelegramCommand::ADD_WALK->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::ADD_WALK->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::ADD_WALK->getDescription();
    }

    public function getSortOrder(): int
    {
        return 400;
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
     * @return AddWalkData
     */
    protected function createData(Update $update): AddWalkData
    {
        /** @var AddWalkData $data */
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

        /** @var AddWalkData $data */
        $command = new AddWalkCommand(
            userId: $this->user->id,
            steps: $data->steps,
        );

        /** @var AddWalkCommandResult $result */
        $result = $this->commandBus->dispatch($command);

        $this->sendSuccessMessage($api, $chatId, $result->toArray());
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/add-walk.html.twig';
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddWalkData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step0(BotApi $api, Update $update, string $chatId, AddWalkData $data): void
    {
        $this->sendTextMessage($api, $chatId, 'Введи количество пройденных шагов');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AddWalkData $data
     * @return void
     */
    protected function step1(BotApi $api, Update $update, string $chatId, AddWalkData $data): void
    {
        try {
            $steps = new StepsAmount($update->getMessage()?->getText());
            $data->steps = $steps->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен неверное количество шагов (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }
    }
}
