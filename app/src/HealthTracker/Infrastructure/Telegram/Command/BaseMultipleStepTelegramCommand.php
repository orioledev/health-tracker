<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Command;

use App\HealthTracker\Infrastructure\Exception\InvalidParameterException;
use App\HealthTracker\Infrastructure\Telegram\Handler\BaseMultipleStepHandler;
use App\HealthTracker\Infrastructure\Telegram\Handler\MultipleStepHandlerDataInterface;
use App\Shared\Application\Bus\QueryBusInterface;
use BadMethodCallException;
use TelegramBot\Api\BotApi;
use TelegramBot\Api\Exception;
use TelegramBot\Api\InvalidArgumentException;
use TelegramBot\Api\Types\Update;
use Twig\Environment;

abstract class BaseMultipleStepTelegramCommand extends BaseTelegramCommand
{
    public function __construct(
        Environment $twig,
        QueryBusInterface $queryBus,
        protected readonly BaseMultipleStepHandler $handler,
    )
    {
        parent::__construct($twig, $queryBus);
    }

    abstract protected function finalStep(
        BotApi $api,
        Update $update,
        string $chatId,
        MultipleStepHandlerDataInterface $data
    ): void;

    /**
     * @param BotApi $api
     * @param Update $update
     * @return void
     * @throws Exception
     * @throws InvalidArgumentException
     */
    public function executeInternal(BotApi $api, Update $update): void
    {
        $chatId = $this->chatId;

        try {
            $this->beforeExecute($api, $update);

            if (parent::isApplicable($update)) {
                $step = 0;
                $data = $this->createData($update);
            } else {
                $step = $this->handler->getCurrentStep($chatId);
                $data = $this->handler->getData($chatId);
            }

            $method = sprintf('step%d', $step);
            $nextMethod = sprintf('step%d', $step + 1);

            if (!method_exists($this, $method)) {
                throw new BadMethodCallException('Такого шага не существует');
            }

            $this->$method($api, $update, $chatId, $data);

            if (method_exists($this, $nextMethod)) {
                $this->handler->setData($chatId, $data);
                $this->handler->setCurrentStep($chatId, $step + 1);
            } else {
                $this->finalStep($api, $update, $chatId, $data);
                $this->handler->clearData($chatId);
            }
        } catch (InvalidParameterException $e) {
            $prev = $e->getPrevious() ?? $e;
            $errorMessage = $prev->getMessage() . '. Попробуй ввести еще раз';
            $this->sendErrorMessage($api, $chatId, $errorMessage);
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

        $message = $this->getTelegramMessage($update);
        $chatId = $message?->getChat()->getId();

        if (!$chatId) {
            return false;
        }

        return $this->handler->hasData((string)$chatId);
    }

    protected function beforeExecute(BotApi $api, Update $update): void {}

    protected function createData(Update $update): MultipleStepHandlerDataInterface
    {
        return $this->handler->createData();
    }
}
