<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Query\Walk\FindWalksByDate\FindWalksByDateQuery;
use App\HealthTracker\Application\Telegram\Query\Walk\FindWalksByDate\FindWalksByDateQueryResult;
use App\HealthTracker\Application\Telegram\Query\Walk\GetDateWithWalks\GetDateWithWalksQuery;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use App\Shared\Application\Bus\QueryBusInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

final class WalksByDayTelegramCommand extends BaseTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
    )
    {
        parent::__construct($twig, $queryBus);
    }

    public function getName(): string
    {
        return TelegramCommand::WALKS_BY_DAY->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::WALKS_BY_DAY->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::WALKS_BY_DAY->getDescription();
    }

    public function getSortOrder(): int
    {
        return 600;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws DateMalformedStringException
     */
    public function execute(BotApi $api, Update $update): void
    {
        parent::execute($api, $update);

        $message = $this->telegramMessage;
        $chatId = (string)$message?->getChat()->getId();

        $date = $this->getDate($update);
        $date = $date ?: new DateTimeImmutable();

        $messageId = $update->getCallbackQuery()?->getMessage()->getMessageId();

        /** @var FindWalksByDateQueryResult $result */
        $result = $this->queryBus->ask(
            new FindWalksByDateQuery(
                telegramUserId: $this->telegramUser->getId(),
                date: $date,
            ),
        );

        $text = $this->renderTemplate($this->getSuccessMessageTemplate(), $result->toArray());
        $buttons = $this->getButtons($date);
        $inlineKeyboard = new InlineKeyboardMarkup($buttons);

        if ($messageId) {
            $api->editMessageText(
                $chatId,
                $messageId,
                $text,
                'html',
                true,
                $inlineKeyboard
            );
        } else {
            $payload = new MessagePayload(
                chatId: $chatId,
                text: $text,
                replyMarkup: $inlineKeyboard,
            );

            $this->sendApiMessage($api, $payload);
        }
    }

    /**
     * @param Update $update
     * @return bool
     * @throws DateMalformedStringException
     */
    public function isApplicable(Update $update): bool
    {
        if (parent::isApplicable($update)) {
            return true;
        }

        return $this->getDate($update) !== null;
    }

    /**
     * @param Update $update
     * @return DateTimeInterface|null
     * @throws DateMalformedStringException
     */
    private function getDate(Update $update): ?DateTimeInterface
    {
        $regexp = '#' . TelegramCommand::WALKS_BY_DAY->value . '_(\d{4}-\d{2}-\d{2})#';

        if ($update->getMessage() && preg_match($regexp, $update->getMessage()->getText(), $matches)) {
            return new DateTimeImmutable($matches[1]);
        }

        if ($update->getCallbackQuery() && preg_match($regexp, $update->getCallbackQuery()->getData(), $matches)) {
            return new DateTimeImmutable($matches[1]);
        }

        return null;
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/walks-by-day.html.twig';
    }

    private function getButtons(DateTimeInterface $date): array
    {
        $buttons = [];

        $prevDateWithWalks = $this->getPrevDateWithWalks($date);
        $nextDateWithWalks = $this->getNextDateWithWalks($date);

        if ($prevDateWithWalks !== null) {
            $buttons[] = [
                'text' => '<< ' . $prevDateWithWalks->format('d.m.Y'),
                'callback_data' => TelegramCommand::WALKS_BY_DAY->value . '_' . $prevDateWithWalks->format('Y-m-d'),
            ];
        }

        if ($nextDateWithWalks !== null) {
            $buttons[] = [
                'text' => $nextDateWithWalks->format('d.m.Y') . ' >>',
                'callback_data' => TelegramCommand::WALKS_BY_DAY->value . '_' . $nextDateWithWalks->format('Y-m-d'),
            ];
        }

        return [$buttons];
    }

    private function getPrevDateWithWalks(DateTimeInterface $date): ?DateTimeInterface
    {
        return $this->queryBus->ask(
            new GetDateWithWalksQuery(
                telegramUserId: $this->telegramUser->getId(),
                date: $date,
                direction: Direction::PREV
            )
        );
    }

    private function getNextDateWithWalks(DateTimeInterface $date): ?DateTimeInterface
    {
        return $this->queryBus->ask(
            new GetDateWithWalksQuery(
                telegramUserId: $this->telegramUser->getId(),
                date: $date,
                direction: Direction::NEXT
            )
        );
    }
}
