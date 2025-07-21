<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Handler;

use App\HealthTracker\Infrastructure\Telegram\DTO\AddWeightMeasurementData;

class AddWeightMeasurementHandler extends BaseMultipleStepHandler
{
    public function createData(): AddWeightMeasurementData
    {
        return new AddWeightMeasurementData();
    }

    public function getDataClassName(): string
    {
        return AddWeightMeasurementData::class;
    }

    protected function getPrefixStep(): string
    {
        return 'weightMeasurementStep_';
    }

    protected function getPrefixData(): string
    {
        return 'weightMeasurement_';
    }

    /**
     * @inheritdoc
     * @return AddWeightMeasurementData
     */
    public function getData(string $id): AddWeightMeasurementData
    {
        /** @var AddWeightMeasurementData $data */
        $data = parent::getData($id);

        return $data;
    }
}
