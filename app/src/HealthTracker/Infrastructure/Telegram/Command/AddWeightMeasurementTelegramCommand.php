<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement\AddWeightMeasurementCommand;
use App\HealthTracker\Application\Telegram\Command\AddWeightMeasurement\AddWeightMeasurementCommandResult;
use App\HealthTracker\Application\Telegram\Query\CheckUserExistenceByTelegramUserId\CheckUserExistenceByTelegramUserIdQuery;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Throwable;
use Twig\Environment;

final class AddWeightMeasurementTelegramCommand extends BaseTelegramCommand implements PublicCommandInterface
{
    public const string NAME = 'test';

    public function __construct(
        Environment $twig,
        private readonly QueryBusInterface $queryBus,
        private readonly CommandBusInterface $commandBus,
    )
    {
        parent::__construct($twig);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getDescription(): string
    {
        return 'Добавить взвешивание';
    }

    public function getAliases(): array
    {
        return [
            '/addWeight',
            '/addweight',
        ];
    }

    public function getExamples(): array
    {
        return [
            '/addweight 57.5',
        ];
    }

    public function getSortOrder(): int
    {
        return 200;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function execute(BotApi $api, Update $update): void
    {
        $chat = $update->getMessage()?->getChat();
        $from = $update->getMessage()?->getFrom();
        $weight = $this->getCommandParameters($update);
        $weight = $weight !== null ? trim($weight) : '';

        try {
            if(empty($weight)) {
                throw new InvalidParameterException('Нужно ввести свой текущий вес');
            }

            $isUserExists = $this->queryBus->ask(
                new CheckUserExistenceByTelegramUserIdQuery($from->getId())
            );

            if (!$isUserExists) {
                $this->sendNeedAcquaintanceMessage($api, $chat->getId());
                return;
            }

            /** @var AddWeightMeasurementCommandResult $result */
            $result = $this->commandBus->dispatch(
                new AddWeightMeasurementCommand(
                    telegramUserId: $from->getId(),
                    weight: $weight ?: 0,
                )
            );

        } catch (Throwable $e) {
            $prev = $e->getPrevious() ?? $e;
            $this->sendErrorMessage($api, $chat->getId(), $prev->getMessage());
            return;
        }

        $this->sendSuccessMessage($api, $chat->getId(), $result->toArray());
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/add-weight-measurement.html.twig';
    }
}
