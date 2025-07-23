<?php

namespace App\Tests\Unit\Domain\Enum;

use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\Tests\Unit\BaseTestCase;

class ActivityLevelTest extends BaseTestCase
{
    public function testDailyNormCaloriesAmountCoefficient(): void
    {
        $this->assertEquals(1.2, ActivityLevel::SEDENTARY->getDailyNormCaloriesAmountCoefficient());
        $this->assertEquals(1.375, ActivityLevel::LOW->getDailyNormCaloriesAmountCoefficient());
        $this->assertEquals(1.55, ActivityLevel::MIDDLE->getDailyNormCaloriesAmountCoefficient());
        $this->assertEquals(1.725, ActivityLevel::HIGH->getDailyNormCaloriesAmountCoefficient());
        $this->assertEquals(1.9, ActivityLevel::VERY_HIGH->getDailyNormCaloriesAmountCoefficient());
    }

    public function testTryFrom(): void
    {
        $this->assertEquals(ActivityLevel::SEDENTARY, ActivityLevel::tryFrom(1));
        $this->assertEquals(ActivityLevel::LOW, ActivityLevel::tryFrom(2));
        $this->assertEquals(ActivityLevel::MIDDLE, ActivityLevel::tryFrom(3));
        $this->assertEquals(ActivityLevel::HIGH, ActivityLevel::tryFrom(4));
        $this->assertEquals(ActivityLevel::VERY_HIGH, ActivityLevel::tryFrom(5));
    }

    public function testInvalidTryFrom(): void
    {
        $this->assertNull(ActivityLevel::tryFrom(99));
    }

    public function testGetLabel(): void
    {
        $this->assertEquals('Сидячий образ жизни', ActivityLevel::SEDENTARY->getLabel());
        $this->assertEquals('Легкая активность (упражнения 1-3 раза в неделю)', ActivityLevel::LOW->getLabel());
        $this->assertEquals('Умеренная активность (упражнения 3-5 раз в неделю)', ActivityLevel::MIDDLE->getLabel());
        $this->assertEquals('Высокая активность (упражнения 6-7 раз в неделю)', ActivityLevel::HIGH->getLabel());
        $this->assertEquals('Очень высокая активность (упражнения каждый день или физическая работа)', ActivityLevel::VERY_HIGH->getLabel());
    }
}
