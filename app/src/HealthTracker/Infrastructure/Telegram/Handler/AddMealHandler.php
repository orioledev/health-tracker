<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Handler;

use App\HealthTracker\Infrastructure\Telegram\DTO\AddMealData;

class AddMealHandler extends BaseMultipleStepHandler
{
    public function createData(): AddMealData
    {
        return new AddMealData();
    }

    public function getDataClassName(): string
    {
        return AddMealData::class;
    }

    protected function getPrefixStep(): string
    {
        return 'mealStep_';
    }

    protected function getPrefixData(): string
    {
        return 'meal_';
    }

    /**
     * @inheritdoc
     * @return AddMealData
     */
    public function getData(string $id): AddMealData
    {
        /** @var AddMealData $data */
        $data = parent::getData($id);

        return $data;
    }
}
