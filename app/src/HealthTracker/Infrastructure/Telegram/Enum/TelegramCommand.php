<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Enum;

enum TelegramCommand: string
{
    case START = '/start';
    case HELP = '/help';
    case ADD_WEIGHT_MEASUREMENT = '/addweight';
    case ADD_MEAL = '/addmeal';
    case ADD_WALK = '/addwalk';
    case MEALS_BY_DAY = '/mealsbyday';

    public function getAlias(): string
    {
        return match ($this) {
            self::START => 'Знакомство',
            self::HELP => 'Помощь',
            self::ADD_WEIGHT_MEASUREMENT => 'Добавить взвешивание',
            self::ADD_MEAL => 'Добавить прием пищи',
            self::ADD_WALK => 'Добавить прогулку',
            self::MEALS_BY_DAY => 'Показать приемы пищи за день',
        };
    }
}
