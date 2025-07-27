<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Command\CreateUser\CreateUserCommand;
use App\HealthTracker\Application\Telegram\Command\CreateUser\CreateUserCommandResult;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Exception\UserAlreadyExistsException;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Telegram\DTO\AcquaintanceUserData;
use App\HealthTracker\Infrastructure\Telegram\Enum\TelegramCommand;
use App\HealthTracker\Infrastructure\Telegram\Handler\AcquaintanceHandler;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\Update;
use Twig\Environment;
use ValueError;

final class AcquaintanceTelegramCommand extends BaseMultipleStepTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        AcquaintanceHandler $handler,
        private readonly CommandBusInterface $commandBus,
    )
    {
        parent::__construct($twig, $queryBus, $handler);
    }

    public function getName(): string
    {
        return TelegramCommand::START->value;
    }

    public function getAliases(): array
    {
        return [
            TelegramCommand::START->getAlias(),
        ];
    }

    public function getDescription(): string
    {
        return TelegramCommand::START->getDescription();
    }

    public function getSortOrder(): int
    {
        return 100;
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws UserAlreadyExistsException
     */
    protected function beforeExecute(BotApi $api, Update $update): void
    {
        if ($this->isUserExists) {
            throw new UserAlreadyExistsException(
                'Мы уже познакомились с тобой. Для того, чтобы узнать, что я умею, нажми ' . TelegramCommand::HELP->value
            );
        }
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

        $telegramUser = $this->telegramUser;

        /** @var AcquaintanceUserData $data */
        $command = new CreateUserCommand(
            telegramUserId: $telegramUser->getId(),
            telegramUsername: $telegramUser->getUsername(),
            firstName: $telegramUser->getFirstName(),
            lastName: $telegramUser->getLastName(),
            birthdate: $data->birthdate,
            gender: $data->gender,
            height: $data->height,
            initialWeight: $data->initialWeight,
            targetWeight: $data->targetWeight,
            activityLevel: $data->activityLevel,
        );

        /** @var CreateUserCommandResult $result */
        $result = $this->commandBus->dispatch($command);

        $this->sendSuccessMessage($api, $chatId, $result->toArray());
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/acquaintance/success.html.twig';
    }

    protected function getWelcomeTemplate(): string
    {
        return 'health-tracker/telegram/command/acquaintance/welcome.html.twig';
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step0(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        $telegramUser = $this->telegramUser;

        $this->sendMessageWithTemplate(
            $api,
            $chatId,
            $this->getWelcomeTemplate(),
            [
                'userData' => [
                    'telegramUserId' => $telegramUser->getId(),
                    'telegramUsername' => $telegramUser->getUsername(),
                    'firstName' => $telegramUser->getFirstName(),
                    'lastName' => $telegramUser->getLastName(),
                ],
            ],
        );

        // Gender buttons
        $buttons = [];
        foreach (Gender::getList() as $value => $label) {
            $buttons[] = [
                ['text' => $label, 'callback_data' => 'gender_' . $value]
            ];
        }

        $payload = new MessagePayload(
            chatId: $chatId,
            text: 'Выбери свой пол',
            replyMarkup: new InlineKeyboardMarkup($buttons),
        );

        $this->sendApiMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step1(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        $genderEnumValue = $this->getEnumValue($update);

        try {
            $gender = Gender::from((int)$genderEnumValue);
            $data->gender = $gender;
        } catch (ValueError) {
            throw new InvalidParameterException('Выбран некорректный пол');
        }

        $this->sendTextMessage($api, $chatId, 'Введи свою дату рождения (дд.мм.гггг)');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step2(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        try {
            $birthdate = new DateTimeImmutable($update->getMessage()?->getText());
            $data->birthdate = $birthdate;
        } catch (DateMalformedStringException) {
            throw new InvalidParameterException('Введена некорректная дата рождения (дд.мм.гггг)');
        }

        $this->sendTextMessage($api, $chatId, 'Введи свой рост (см)');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step3(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        try {
            $height = new Height((int)$update->getMessage()?->getText());
            $data->height = $height->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный рост (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        $this->sendTextMessage($api, $chatId, 'Введи свой текущий вес (кг)');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step4(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        try {
            $weight = new Weight($update->getMessage()?->getText());
            $data->initialWeight = $weight->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный текущий вес (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        $this->sendTextMessage($api, $chatId, 'Введи вес, к которому ты стремишься (кг)');
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step5(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        try {
            $weight = new Weight($update->getMessage()?->getText());
            $data->targetWeight = $weight->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный вес, к которому ты стремишься (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        // Activity level buttons
        $buttons = [];
        foreach (ActivityLevel::getList() as $value => $label) {
            $buttons[] = [
                ['text' => $label, 'callback_data' => 'activityLevel_' . $value]
            ];
        }

        $payload = new MessagePayload(
            chatId: $chatId,
            text: 'Выбери свой уровень физической активности',
            replyMarkup: new InlineKeyboardMarkup($buttons),
        );

        $this->sendApiMessage($api, $payload);
    }

    /**
     * @param BotApi $api
     * @param Update $update
     * @param string $chatId
     * @param AcquaintanceUserData $data
     * @return void
     */
    protected function step6(BotApi $api, Update $update, string $chatId, AcquaintanceUserData $data): void
    {
        $activityLevelEnumValue = $this->getEnumValue($update);

        try {
            $activityLevel = ActivityLevel::from((int)$activityLevelEnumValue);
            $data->activityLevel = $activityLevel;
        } catch (ValueError) {
            throw new InvalidParameterException('Выбран некорректный уровень физической активности');
        }
    }

    private function getEnumValue(Update $update): ?string
    {
        $regexp = '/[a-zA-Z_]+_(\d+)/';

        if ($update->getMessage() && preg_match($regexp, $update->getMessage()->getText(), $matches)) {
            return $matches[1];
        }

        if ($update->getCallbackQuery() && preg_match($regexp, $update->getCallbackQuery()->getData(), $matches)) {
            return $matches[1];
        }

        return null;
    }
}
