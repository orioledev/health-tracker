<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\Command;

use App\HealthTracker\Application\DTO\UserData;
use App\HealthTracker\Application\Query\User\FindUserByTelegramUserId\FindUserByTelegramUserIdQuery;
use App\HealthTracker\Presentation\Exception\InvalidParameterException;
use App\HealthTracker\Presentation\Exception\NeedAcquaintanceException;
use App\HealthTracker\Presentation\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Presentation\Telegram\Message\MessagePayload;
use App\Shared\Application\Bus\QueryBusInterface;
use BoShurik\TelegramBotBundle\Telegram\Command\AbstractCommand;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use TelegramBot\Api\Types\User;
use Throwable;
use Twig\Environment;

abstract class BaseTelegramCommand extends AbstractCommand implements PublicCommandInterface
{
    public const string COMMAND_NAME_REGEXP = '/^([^@]+)$/';

    protected ?Message $telegramMessage = null {
        get {
            return $this->telegramMessage;
        }
    }
    protected ?string $chatId = null {
        get {
            return $this->chatId;
        }
    }
    protected ?User $telegramUser = null {
        get {
            return $this->telegramUser;
        }
    }
    protected ?UserData $user = null {
        get {
            return $this->user;
        }
    }
    protected ?bool $isUserExists = null {
        get {
            return $this->isUserExists;
        }
    }


    public function __construct(
        protected readonly Environment $twig,
        protected readonly QueryBusInterface $queryBus,
    ) {}

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws NeedAcquaintanceException
     */
    abstract protected function executeInternal(BotApi $api, Update $update): void;

    abstract protected function getSuccessMessageTemplate(): string;

    public function getSortOrder(): int
    {
        return 1;
    }

    protected function matchCommandName(string $text, string $name): bool
    {
        preg_match(static::COMMAND_NAME_REGEXP, $text, $matches);

        return !empty($matches) && $matches[1] == $name;
    }

    protected function getCommandParameters(Update $update): ?string
    {
        return null;
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
        $this->telegramMessage = $this->getTelegramMessage($update);
        $this->chatId = (string)$this->telegramMessage?->getChat()->getId();

        $this->telegramUser = $this->getTelegramUser($update);

        try {
            if (!$this->telegramUser) {
                throw new \InvalidArgumentException('Не удалось определить пользователя telegram');
            }

            $this->user = $this->queryBus->ask(
                new FindUserByTelegramUserIdQuery($this->telegramUser->getId())
            );

            $this->isUserExists = $this->user !== null;

            $this->executeInternal($api, $update);
        } catch (InvalidParameterException $e) {
            $prev = $e->getPrevious() ?? $e;
            $errorMessage = $prev->getMessage() . '. Попробуй ввести еще раз';
            $this->sendErrorMessage($api, $this->chatId, $errorMessage);
            return;
        } catch (NeedAcquaintanceException) {
            $this->sendNeedAcquaintanceMessage($api, $this->chatId);
            return;
        } catch (Throwable $e) {
            $prev = $e->getPrevious() ?? $e;
            $this->sendErrorMessage($api, $this->chatId, $prev->getMessage());
            return;
        }
    }

    protected function getTelegramMessage(Update $update): ?Message
    {
        if ($update->getCallbackQuery()) {
            return $update->getCallbackQuery()->getMessage();
        }

        return $update->getMessage();
    }

    protected function getTelegramUser(Update $update): ?User
    {
        if ($update->getCallbackQuery()) {
            return $update->getCallbackQuery()->getFrom();
        }

        return $update->getMessage()?->getFrom();
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
     * @param bool $showMenuButtons
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendSuccessMessage(
        BotApi $api,
        int|string|float $chatId,
        array $context = [],
        bool $showMenuButtons = true,
    ): void
    {
        $this->sendMessageWithTemplate(
            $api,
            $chatId,
            $this->getSuccessMessageTemplate(),
            $context,
            $showMenuButtons
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
            $this->getNeedAcquaintanceMessageTemplate(),
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
     * @param bool $showMenuButtons
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function sendMessageWithTemplate(
        BotApi $api,
        int|string|float $chatId,
        string $template,
        array $templateContext = [],
        bool $showMenuButtons = false,
    ): void
    {
        $text = $this->renderTemplate($template, $templateContext);

        $replyMarkup = $showMenuButtons
            ? $this->renderMenuKeyboard()
            : null;

        $payload = new MessagePayload(
            chatId: $chatId,
            text: $text,
            replyMarkup: $replyMarkup,
        );

        $this->sendApiMessage($api, $payload);
    }

    protected function renderTemplate($template, $templateContext): string
    {
        try {
            $text = $this->twig->render($template, $templateContext);
        } catch (Throwable $e) {
            $text = $e->getMessage();
        }

        return $text;
    }

    protected function renderMenuKeyboard(): ReplyKeyboardMarkup
    {
        $keyboard = [
            [
                TelegramCommand::ADD_WEIGHT_MEASUREMENT->getAlias(),
                TelegramCommand::ADD_MEAL->getAlias(),
            ],
            [
                TelegramCommand::ADD_WALK->getAlias(),
                TelegramCommand::MEALS_BY_DAY->getAlias(),
            ],
            [
                TelegramCommand::WALKS_BY_DAY->getAlias(),
                TelegramCommand::HELP->getAlias(),
            ],
        ];

        return new ReplyKeyboardMarkup(
            keyboard: $keyboard,
            resizeKeyboard: true,
        );
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
