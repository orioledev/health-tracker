<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use BoShurik\TelegramBotBundle\Telegram\Command\CommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\Registry\CommandRegistry;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class HelpTelegramCommand extends BaseTelegramCommand implements PublicCommandInterface
{
    public const string NAME = 'help';

    public function __construct(
        Environment                      $twig,
        private readonly CommandRegistry $commandRegistry,
    )
    {
        parent::__construct($twig);
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function getAliases(): array
    {
        return [
            '/help',
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

            $name = method_exists($command, 'getAliases')
                ? current($command->getAliases())
                : $command->getName();

            $examples = method_exists($command, 'getExamples')
                ? $command->getExamples()
                : [];

            $context['commands'][] = [
                'name' => $name,
                'description' => $command->getDescription(),
                'examples' => $examples,
            ];
        }

        $this->sendSuccessMessage(
            $api,
            $update->getMessage()?->getChat()->getId(),
            $context
        );
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/help.html.twig';
    }
}
