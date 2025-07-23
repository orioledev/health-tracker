<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Handler;

use App\HealthTracker\Infrastructure\Telegram\DTO\AddWalkData;

class AddWalkHandler extends BaseMultipleStepHandler
{
    public function createData(): AddWalkData
    {
        return new AddWalkData();
    }

    public function getDataClassName(): string
    {
        return AddWalkData::class;
    }

    protected function getPrefixStep(): string
    {
        return 'walkStep_';
    }

    protected function getPrefixData(): string
    {
        return 'walk_';
    }

    /**
     * @inheritdoc
     * @return AddWalkData
     */
    public function getData(string $id): AddWalkData
    {
        /** @var AddWalkData $data */
        $data = parent::getData($id);

        return $data;
    }
}
