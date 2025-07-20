<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Application\Telegram\Command\CreateUser\CreateUserCommand;
use App\HealthTracker\Application\Telegram\Command\CreateUser\CreateUserCommandResult;
use App\HealthTracker\Application\Telegram\Query\CheckUserExistenceByTelegramUserId\CheckUserExistenceByTelegramUserIdQuery;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Domain\Exception\UserAlreadyExistsException;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Telegram\DTO\AcquaintanceUserData;
use App\HealthTracker\Infrastructure\Telegram\Handler\AcquaintanceHandler;
use App\HealthTracker\Infrastructure\Telegram\Message\MessagePayload;
use App\Shared\Application\Bus\CommandBusInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use BadMethodCallException;
use BoShurik\TelegramBotBundle\Telegram\Command\PublicCommandInterface;
use DateMalformedStringException;
use DateTimeImmutable;
use Psr\Log\LoggerInterface;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Message;
use TelegramBot\Api\Types\Update;
use Throwable;
use Twig\Environment;
use ValueError;

final class AcquaintanceTelegramCommand extends BaseTelegramCommand implements PublicCommandInterface
{
    public const string NAME = '/start';

    public function __construct(
        Environment $twig,
        private readonly LoggerInterface $logger,
        private readonly AcquaintanceHandler $acquaintanceHandler,
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
        return 'Знакомство';
    }

    public function getSortOrder(): int
    {
        return 100;
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
        $chatId = (string)$update->getMessage()?->getChat()->getId();
        $telegramUser = $update->getMessage()?->getFrom();

        try {
            $isUserExists = $this->queryBus->ask(
                new CheckUserExistenceByTelegramUserIdQuery($telegramUser->getId())
            );

            if ($isUserExists) {
                throw new UserAlreadyExistsException(
                    'Мы уже познакомились с тобой. Для того, чтобы узнать, что я умею, нажми /help'
                );
            }

            if ($this->isCancelStep($update)) {
                $this->cancelStep($api, $update->getMessage(), $chatId);
                return;
            }

            if (parent::isApplicable($update)) {
                $step = 0;
                $userData = $this->acquaintanceHandler->createUserData();
                $userData->fillTelegramUserData($telegramUser);
            } else {
                $step = $this->acquaintanceHandler->getCurrentStep($chatId);
                $userData = $this->acquaintanceHandler->getUserData($chatId);
            }

            $method = sprintf('step%d', $step);
            $nextMethod = sprintf('step%d', $step + 1);

            if (!method_exists($this, $method)) {
                throw new BadMethodCallException('Такого шага не существует');
            }

            $this->$method($api, $update->getMessage(), $chatId, $userData);

            if (method_exists($this, $nextMethod)) {
                $this->acquaintanceHandler->setUserData($chatId, $userData);
                $this->acquaintanceHandler->setCurrentStep($chatId, $step + 1);
            } else {
                $this->finalStep($api, $update->getMessage(), $chatId, $userData);
                $this->acquaintanceHandler->clearData($chatId);
            }
        } catch (InvalidParameterException $e) {
            $prev = $e->getPrevious() ?? $e;
            $errorMessage = $prev->getMessage() . '. Попробуй ввести еще раз';
            $this->sendErrorMessage($api, $chatId, $errorMessage);
            return;
        } catch (Throwable $e) {
            $prev = $e->getPrevious() ?? $e;
            $this->sendErrorMessage($api, $chatId, $prev->getMessage());
            return;
        }
    }

    /**
     * @param Update $update
     * @return bool
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function isApplicable(Update $update): bool
    {
        if (parent::isApplicable($update)) {
            return true;
        }
        if (!$update->getMessage()) {
            return false;
        }

        return $this->acquaintanceHandler->hasData((string)$update->getMessage()->getChat()->getId());
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step0(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step0');

        $payload = new MessagePayload(
            chatId: $chatId,
            template: $this->getWelcomeTemplate(),
            templateContext: [
                'userData' => $userData->toArray(),
            ],
        );

        $this->sendMessageWithTemplate($api, $payload);

        $this->sendTextMessage($api, $chatId, 'Выбери свой пол');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step1(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step1, gender: ' . $message->getText());

        try {
            $gender = Gender::from((int)$message->getText());
            $userData->gender = $gender;
        } catch (ValueError) {
            throw new InvalidParameterException('Выбран некорректный пол');
        }

        $this->sendTextMessage($api, $chatId, 'Введи свою дату рождения (в формате дд.мм.гггг)');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step2(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step2, birthdate: ' . $message->getText());

        try {
            $birthdate = new DateTimeImmutable($message->getText());
            $userData->birthdate = $birthdate;
        } catch (DateMalformedStringException) {
            throw new InvalidParameterException('Введена некорректная дата рождения (требуемый формат - дд.мм.гггг)');
        }

        $this->sendTextMessage($api, $chatId, 'Введи свой рост (см)');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step3(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step3, height: ' . $message->getText());

        try {
            $height = new Height((int)$message->getText());
            $userData->height = $height->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный рост (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        $this->sendTextMessage($api, $chatId, 'Введи свой текущий вес (кг)');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step4(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step4, initialWeight: ' . $message->getText());

        try {
            $weight = new Weight($message->getText());
            $userData->initialWeight = $weight->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный текущий вес (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        $this->sendTextMessage($api, $chatId, 'Введи вес, к которому ты стремишься (кг)');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step5(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step5, targetWeight: ' . $message->getText());

        try {
            $weight = new Weight($message->getText());
            $userData->targetWeight = $weight->value();
        } catch (\InvalidArgumentException $e) {
            $errorMessage = sprintf('Введен некорректный вес, к которому ты стремишься (%s)', $e->getMessage());
            throw new InvalidParameterException($errorMessage);
        }

        $this->sendTextMessage($api, $chatId, 'Выбери свою цель');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function step6(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step6, weightTargetType: ' . $message->getText());

        try {
            $weightTargetType = WeightTargetType::from((int)$message->getText());
            $userData->weightTargetType = $weightTargetType;
        } catch (ValueError) {
            throw new InvalidParameterException('Выбрана некорректная цель');
        }

        $this->sendTextMessage($api, $chatId, 'Выбери свой уровень физической активности');
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     */
    protected function step7(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('step7, activityLevel: ' . $message->getText());

        try {
            $activityLevel = ActivityLevel::from((int)$message->getText());
            $userData->activityLevel = $activityLevel;
        } catch (ValueError) {
            throw new InvalidParameterException('Выбран некорректный уровень физической активности');
        }
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @param AcquaintanceUserData $userData
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    protected function finalStep(BotApi $api, Message $message, string $chatId, AcquaintanceUserData $userData): void
    {
        $this->logger->debug('finalStep');

        $command = new CreateUserCommand(
            telegramUserId: $userData->telegramUserId,
            telegramUsername: $userData->telegramUsername,
            firstName: $userData->firstName,
            lastName: $userData->lastName,
            birthdate: $userData->birthdate,
            gender: $userData->gender,
            height: $userData->height,
            initialWeight: $userData->initialWeight,
            targetWeight: $userData->targetWeight,
            activityLevel: $userData->activityLevel,
            weightTargetType: $userData->weightTargetType,
        );

        /** @var CreateUserCommandResult $result */
        $result = $this->commandBus->dispatch($command);

        $this->sendSuccessMessage($api, $chatId, $result->toArray());
    }

    /**
     * @param BotApi $api
     * @param Message $message
     * @param string $chatId
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     * @throws \Psr\Cache\InvalidArgumentException
     */
    protected function cancelStep(BotApi $api, Message $message, string $chatId): void
    {
        $this->logger->debug('cancelStep');

        $this->acquaintanceHandler->clearData($chatId);

        $payload = new MessagePayload(
            chatId: $chatId,
            template: $this->getCancelTemplate(),
        );

        $this->sendMessageWithTemplate($api, $payload);
    }

    protected function isCancelStep(Update $update): bool
    {
        if (!parent::isApplicable($update)) {
            return false;
        }

        $text = $update->getMessage()?->getText() ?: '';
        preg_match(self::REGEXP, $text, $matches);

        return mb_strtolower($matches[3]) === 'cancel';
    }

    protected function getSuccessMessageTemplate(): string
    {
        return 'health-tracker/telegram/command/acquaintance/success.html.twig';
    }

    protected function getWelcomeTemplate(): string
    {
        return 'health-tracker/telegram/command/acquaintance/welcome.html.twig';
    }

    protected function getCancelTemplate(): string
    {
        return 'health-tracker/telegram/command/acquaintance/cancel.html.twig';
    }
}
