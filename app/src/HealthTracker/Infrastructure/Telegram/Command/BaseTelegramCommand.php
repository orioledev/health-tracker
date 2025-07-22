<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User;
use Throwable;
use Twig\Environment;

abstract class BaseTelegramCommand extends AbstractCommand implements PublicCommandInterface
{
    public const string COMMAND_NAME_REGEXP = '/^([^@]+)$/';

    private ?Message $_telegramMessage = null;
    private ?User $_telegramUser = null;

    public function __construct(
        protected readonly Environment $twig,
    ) {}

    public function getExamples(): array
    {
        return [];
    }

    public function getSortOrder(): int
    {
        return 1;
    }

    abstract protected function getSuccessMessageTemplate(): string;

    protected function matchCommandName(string $text, string $name): bool
    {
        preg_match(static::COMMAND_NAME_REGEXP, $text, $matches);

        return !empty($matches) && $matches[1] == $name;
    }

    protected function getCommandParameters(Update $update): ?string
    {
        return null;
    }

    protected function getTelegramMessage(Update $update): ?Message
    {
        if ($this->_telegramMessage !== null) {
            return $this->_telegramMessage;
        }

        if ($update->getCallbackQuery()) {
            $this->_telegramMessage = $update->getCallbackQuery()->getMessage();
        } else {
            $this->_telegramMessage = $update->getMessage();
        }

        return $this->_telegramMessage;
    }

    protected function getTelegramUser(Update $update): ?User
    {
        if ($this->_telegramUser !== null) {
            return $this->_telegramUser;
        }

        if ($update->getCallbackQuery()) {
            $this->_telegramUser = $update->getCallbackQuery()->getFrom();
        } else {
            $this->_telegramUser = $update->getMessage()?->getFrom();
        }

        return $this->_telegramUser;
    }

    protected function getErrorMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/error.html.twig';
    }

    protected function getNeedAcquaintanceMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/need-acquaintance.html.twig';
    }

    /**
     * @param BotApi $api
     * @param int|string|float $chatId
     * @param string|null $message
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendErrorMessage(BotApi $api, int|string|float $chatId, ?string $message = null): void
    {
        $context = [
            'description' => $message ?: 'Что-то пошло не так. Пожалуйста, попробуйте позже.',
        ];

        $this->sendMessageWithTemplate(
            $api,
            $chatId,
            $this->getErrorMessageTemplate(),
            $context
        );
    }

    /**
     * @param BotApi $api
     * @param int|string|float $chatId
     * @param array $context
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendSuccessMessage(BotApi $api, int|string|float $chatId, array $context = []): void
    {
        $this->sendMessageWithTemplate(
            $api,
            $chatId,
            $this->getSuccessMessageTemplate(),
            $context
        );
    }

    /**
     * @param BotApi $api
     * @param int|string|float $chatId
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendNeedAcquaintanceMessage(BotApi $api, int|string|float $chatId): void
    {
        $this->sendMessageWithTemplate(
            $api,
            $chatId,
            $this->getNeedAcquaintanceMessageTemplate()
        );
    }

    /**
     * @param BotApi $api
     * @param int|string|float $chatId
     * @param string $text
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendTextMessage(BotApi $api, int|string|float $chatId, string $text): void
    {
        $payload = new MessagePayload(
            chatId: $chatId,
            text: $text
        );

        $this->sendApiMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param int|string|float $chatId
     * @param string $template
     * @param array $templateContext
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendMessageWithTemplate(
        BotApi $api,
        int|string|float $chatId,
        string $template,
        array $templateContext = [],
    ): void
    {
        try {
            $text = $this->twig->render($template, $templateContext);
        } catch (Throwable $e) {
            $text = $e->getMessage();
        }

        $payload = new MessagePayload(
            chatId: $chatId,
            text: $text
        );

        $this->sendApiMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param MessagePayload $payload
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendApiMessage(BotApi $api, MessagePayload $payload): void
    {
        $api->sendMessage(
            chatId: $payload->chatId,
            text: $payload->text,
            parseMode: $payload->parseMode,
            disablePreview: $payload->disablePreview,
            replyToMessageId: $payload->replyToMessageId,
            replyMarkup: $payload->replyMarkup,
            disableNotification: $payload->disableNotification,
            messageThreadId: $payload->messageThreadId,
            protectContent: $payload->protectContent,
            allowSendingWithoutReply: $payload->allowSendingWithoutReply,
        );
    }
}
