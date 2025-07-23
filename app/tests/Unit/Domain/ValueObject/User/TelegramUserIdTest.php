<?php

namespace App\Tests\Unit\Domain\ValueObject\User;

use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Tests\Unit\BaseTestCase;

class TelegramUserIdTest extends BaseTestCase
{
    public function testCreateValid(): void
    {
        $userId = new TelegramUserId('123456789');

        $this->assertEquals('123456789', $userId->value());
        $this->assertEquals('123456789', (string) $userId);
    }

    public function testEquality(): void
    {
        $userId1 = new TelegramUserId('123456789');
        $userId2 = new TelegramUserId('123456789');
        $userId3 = new TelegramUserId('987654321');

        $this->assertTrue($userId1->equals($userId2));
        $this->assertFalse($userId1->equals($userId3));
    }
}
