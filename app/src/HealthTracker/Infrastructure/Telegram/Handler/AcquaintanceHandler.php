<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Handler;

use App\HealthTracker\Infrastructure\Telegram\DTO\AcquaintanceUserData;

class AcquaintanceHandler extends BaseMultipleStepHandler
{
    public function createData(): AcquaintanceUserData
    {
        return new AcquaintanceUserData();
    }

    public function getDataClassName(): string
    {
        return AcquaintanceUserData::class;
    }

    protected function getPrefixStep(): string
    {
        return 'acquaintanceStep_';
    }

    protected function getPrefixData(): string
    {
        return 'acquaintanceUser_';
    }

    /**
     * @inheritdoc
     * @return AcquaintanceUserData
     */
    public function getData(string $id): AcquaintanceUserData
    {
        /** @var AcquaintanceUserData $data */
        $data = parent::getData($id);

        return $data;
    }
}
