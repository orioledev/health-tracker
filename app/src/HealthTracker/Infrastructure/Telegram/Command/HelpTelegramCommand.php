<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\Shared\Application\Bus\QueryBusInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class HelpTelegramCommand extends BaseTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        private readonly CommandRegistry $commandRegistry,
    )
    {
        parent::__construct($twig, $queryBus);
    }

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
        return 'Отображает справочную информацию';
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

        $commands = $this->commandRegistry->getCommands();

        usort($commands, function (CommandInterface $a, CommandInterface $b): int {
            if (!method_exists($a, 'getSortOrder') || !method_exists($b, 'getSortOrder')) {
                return -1;
            }

            return $a->getSortOrder() > $b->getSortOrder() ? 1 : -1;
        });

        $context = ['commands' => []];

        foreach ($commands as $command) {
            if (!$command instanceof PublicCommandInterface) {
                continue;
            }

            if ($command instanceof self) {
                continue;
            }

            $name = $command->getName();
            $alias = method_exists($command, 'getAliases')
                ? current($command->getAliases())
                : null;

            $isStartCommand = $name === TelegramCommand::START->value || $alias === TelegramCommand::START->getAlias();

            if ($this->isUserExists) {
                if ($isStartCommand) {
                    continue;
                }
            } else {
                if (!$isStartCommand) {
                    continue;
                }
            }

            $context['commands'][] = [
                'name' => $name,
                'alias' => $alias,
                'description' => $command->getDescription(),
            ];
        }

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
