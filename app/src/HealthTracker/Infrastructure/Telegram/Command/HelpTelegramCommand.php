<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;

final class HelpTelegramCommand extends BaseTelegramCommand
{
    public function getName(): string
    {
        return TelegramCommand::HELP->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::HELP->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::HELP->getDescription();
    }

    public function getSortOrder(): int
    {
        return 99999;
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
        parent::execute($api, $update);

        if ($this->isUserExists) {
            $commandsList = TelegramCommand::getHelpCommandsListForRegisteredUser();
        } else {
            $commandsList = TelegramCommand::getHelpCommandsListForNewUser();
        }

        $commands = [];
        foreach ($commandsList as $command) {
            $commands[] = [
                'name' => $command->value,
                'alias' => $command->getAlias(),
                'description' => $command->getDescription(),
            ];
        }

        $context = [
            'commands' => $commands,
        ];

        $this->sendSuccessMessage(
            $api,
            $update->getMessage()?->getChat()->getId(),
            $context,
            false
        );
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/help.html.twig';
    }
}
