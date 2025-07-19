<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use Throwable;
use Twig\Environment;

abstract class BaseTelegramCommand extends AbstractCommand implements PublicCommandInterface
{
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
     * @param mixed $chatId
     * @param string|null $message
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendErrorMessage(BotApi $api, mixed $chatId, ?string $message = null): void
    {
        $context = [
            'description' => $message ?: 'Что-то пошло не так. Пожалуйста, попробуйте позже.',
        ];

        $payload = new MessagePayload(
            chatId: $chatId,
            template: $this->getErrorMessageTemplate(),
            templateContext: $context
        );

        $this->sendMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param mixed $chatId
     * @param array $context
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendSuccessMessage(BotApi $api, mixed $chatId, array $context = []): void
    {
        $payload = new MessagePayload(
            chatId: $chatId,
            template: $this->getSuccessMessageTemplate(),
            templateContext: $context
        );

        $this->sendMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param mixed $chatId
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendNeedAcquaintanceMessage(BotApi $api, mixed $chatId): void
    {
        $payload = new MessagePayload(
            chatId: $chatId,
            template: $this->getNeedAcquaintanceMessageTemplate(),
        );

        $this->sendMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param MessagePayload $payload
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendMessage(BotApi $api, MessagePayload $payload): void
    {
        try {
            $text = $this->twig->render($payload->template, $payload->templateContext);
        } catch (Throwable $e) {
            $text = $e->getMessage();
        }

        $api->sendMessage(
            chatId: $payload->chatId,
            text: $text,
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
