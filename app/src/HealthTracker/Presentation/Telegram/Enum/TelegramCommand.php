<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\Enum;

enum TelegramCommand: string
{
    case START = '/start';
    case HELP = '/help';
    case ADD_WEIGHT_MEASUREMENT = '/addweight';
    case ADD_MEAL = '/addmeal';
    case ADD_WALK = '/addwalk';
    case MEALS_BY_DAY = '/mealsbyday';
    case WALKS_BY_DAY = '/walksbyday';

    public function getAlias(): string
    {
        return match ($this) {
            self::START => 'Знакомство',
            self::HELP => 'Помощь',
            self::ADD_WEIGHT_MEASUREMENT => 'Добавить взвешивание',
            self::ADD_MEAL => 'Добавить прием пищи',
            self::ADD_WALK => 'Добавить прогулку',
            self::MEALS_BY_DAY => 'Показать приемы пищи за день',
            self::WALKS_BY_DAY => 'Показать прогулки за день',
        };
    }

    public function getDescription(): string
    {
        return match ($this) {
            self::START => 'Знакомство с пользователем',
            self::HELP => 'Отображает справочную информацию',
            self::ADD_WEIGHT_MEASUREMENT => 'Добавление нового взвешивания',
            self::ADD_MEAL => 'Добавление нового приема пищи',
            self::ADD_WALK => 'Добавление прогулки',
            self::MEALS_BY_DAY => 'Просмотр истории приемов пищи за день',
            self::WALKS_BY_DAY => 'Просмотр истории прогулок за день',
        };
    }

    public static function getHelpCommandsListForNewUser(): array
    {
        return [
            self::START,
        ];
    }

    public static function getHelpCommandsListForRegisteredUser(): array
    {
        return [
            self::ADD_WEIGHT_MEASUREMENT,
            self::ADD_MEAL,
            self::ADD_WALK,
            self::MEALS_BY_DAY,
            self::WALKS_BY_DAY,
        ];
    }
}
