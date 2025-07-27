<?php

declare(strict_types=1);

namespace App\Tests\Unit\Domain\Entity;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\UserDailyNorm;
use App\HealthTracker\Domain\Entity\UserIndicator;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\Gender;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\HealthTracker\Domain\ValueObject\User\FullName;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\TelegramUsername;
use App\Tests\Unit\BaseTestCase;
use DateTimeImmutable;

final class UserTest extends BaseTestCase
{
    private User $user;
    private TelegramUserId $telegramUserId;
    private TelegramUsername $telegramUsername;
    private FullName $fullName;

    protected function setUp(): void
    {
        $this->telegramUserId = new TelegramUserId(123456789);
        $this->telegramUsername = new TelegramUsername('test_user');
        $this->fullName = new FullName('John', 'Doe');

        $this->user = new User(
            $this->telegramUserId,
            $this->telegramUsername,
            $this->fullName
        );
    }

    public function testUserCreation(): void
    {
        self::assertSame($this->telegramUserId->value(), $this->user->telegramUserId->value());
        self::assertSame($this->telegramUsername->value(), $this->user->telegramUsername->value());
        self::assertSame($this->fullName->value(), $this->user->fullName->value());
    }

    public function testIsFilledWithNoData(): void
    {
        self::assertFalse($this->user->isFilled());
    }

    public function testIsFilledWithPartialData(): void
    {
        $this->user->gender = Gender::MALE;
        $this->user->birthdate = new DateTimeImmutable('1990-01-01');

        self::assertFalse($this->user->isFilled());
    }

    public function testIsFilledWithIndicatorButNoDailyNorm(): void
    {
        // Mock the indicator
        $indicator = $this->createMock(UserIndicator::class);
        $indicator->method('isFilled')->willReturn(true);

        $this->user->indicator = $indicator;
        $this->user->gender = Gender::MALE;
        $this->user->birthdate = new DateTimeImmutable('1990-01-01');

        self::assertFalse($this->user->isFilled());
    }

    public function testIsFilledWithAllData(): void
    {
        // Mock the indicator
        $indicator = $this->createMock(UserIndicator::class);
        $indicator->method('isFilled')->willReturn(true);

        // Mock the daily norm
        $dailyNorm = $this->createMock(UserDailyNorm::class);

        $this->user->indicator = $indicator;
        $this->user->dailyNorm = $dailyNorm;
        $this->user->gender = Gender::MALE;
        $this->user->birthdate = new DateTimeImmutable('1990-01-01');

        self::assertTrue($this->user->isFilled());
    }

    public function testIsFilledWithoutValidation(): void
    {
        // Mock the indicator
        $indicator = $this->createMock(UserIndicator::class);
        $indicator->method('isFilled')->willReturn(true);

        $this->user->indicator = $indicator;
        $this->user->gender = Gender::MALE;
        $this->user->birthdate = new DateTimeImmutable('1990-01-01');

        self::assertTrue($this->user->isFilled(false));
    }

    public function testHasIndicator(): void
    {
        self::assertFalse($this->user->hasIndicator());

        $this->user->indicator = $this->createMock(UserIndicator::class);

        self::assertTrue($this->user->hasIndicator());
    }

    public function testHasDailyNorm(): void
    {
        self::assertFalse($this->user->hasDailyNorm());

        $this->user->dailyNorm = new UserDailyNorm(
            $this->user,
            new Macronutrients(2000, 150, 70, 250),
            new StepsAmount(10000)
        );

        self::assertTrue($this->user->hasDailyNorm());
    }

    public function testGetAgeWithNoBirthdate(): void
    {
        self::assertNull($this->user->getAge());
    }

    public function testGetAge(): void
    {
        $birthdate = new DateTimeImmutable('1990-01-01');
        $this->user->birthdate = $birthdate;

        $expectedAge = (new DateTimeImmutable())->diff($birthdate)->y;
        self::assertSame($expectedAge, $this->user->getAge());
    }
}
