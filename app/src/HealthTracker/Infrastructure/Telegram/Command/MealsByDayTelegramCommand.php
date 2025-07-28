<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Query\Meal\FindMealsByDate\FindMealsByDateQuery;
use App\HealthTracker\Application\Query\Meal\FindMealsByDate\FindMealsByDateQueryResult;
use App\HealthTracker\Application\Query\Meal\GetDateWithMeals\GetDateWithMealsQuery;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Infrastructure\Exception\NeedAcquaintanceException;
use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use DateMalformedStringException;
use DateTimeImmutable;
use DateTimeInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;

final class MealsByDayTelegramCommand extends BaseTelegramCommand
{
    public function getName(): string
    {
        return TelegramCommand::MEALS_BY_DAY->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::MEALS_BY_DAY->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::MEALS_BY_DAY->getDescription();
    }

    public function getSortOrder(): int
    {
        return 500;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws NeedAcquaintanceException
     * @throws DateMalformedStringException
     */
    public function executeInternal(BotApi $api, Update $update): void
    {
        if (!$this->isUserExists) {
            throw new NeedAcquaintanceException();
        }

        $chatId = $this->chatId;

        $date = $this->getDate($update);
        $date = $date ?: new DateTimeImmutable();

        $messageId = $update->getCallbackQuery()?->getMessage()->getMessageId();

        /** @var FindMealsByDateQueryResult $result */
        $result = $this->queryBus->ask(
            new FindMealsByDateQuery(
                userId: $this->user->id,
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
        $regexp = '#' . TelegramCommand::MEALS_BY_DAY->value . '_(\d{4}-\d{2}-\d{2})#';

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
        return 'health-tracker/telegram/command/meals-by-day.html.twig';
    }

    private function getButtons(DateTimeInterface $date): array
    {
        $buttons = [];

        $prevDateWithMeals = $this->getPrevDateWithMeals($date);
        $nextDateWithMeals = $this->getNextDateWithMeals($date);

        if ($prevDateWithMeals !== null) {
            $buttons[] = [
                'text' => '<< ' . $prevDateWithMeals->format('d.m.Y'),
                'callback_data' => TelegramCommand::MEALS_BY_DAY->value . '_' . $prevDateWithMeals->format('Y-m-d'),
            ];
        }

        if ($nextDateWithMeals !== null) {
            $buttons[] = [
                'text' => $nextDateWithMeals->format('d.m.Y') . ' >>',
                'callback_data' => TelegramCommand::MEALS_BY_DAY->value . '_' . $nextDateWithMeals->format('Y-m-d'),
            ];
        }

        return [$buttons];
    }

    private function getPrevDateWithMeals(DateTimeInterface $date): ?DateTimeInterface
    {
        return $this->queryBus->ask(
            new GetDateWithMealsQuery(
                userId: $this->user->id,
                date: $date,
                direction: Direction::PREV
            )
        );
    }

    private function getNextDateWithMeals(DateTimeInterface $date): ?DateTimeInterface
    {
        return $this->queryBus->ask(
            new GetDateWithMealsQuery(
                userId: $this->user->id,
                date: $date,
                direction: Direction::NEXT
            )
        );
    }
}
