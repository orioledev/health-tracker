<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Telegram\Message;

use TelegramBot\Api\Types\ForceReply;
use TelegramBot\Api\Types\Inline\InlineKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardMarkup;
use TelegramBot\Api\Types\ReplyKeyboardRemove;

final readonly class MessagePayload
{
    public function __construct(
        public int|string|float $chatId,
        public string $text,
        public ?string $parseMode = 'html',
        public bool $disablePreview = true,
        public ?int $replyToMessageId = null,
        public InlineKeyboardMarkup|ReplyKeyboardMarkup|ReplyKeyboardRemove|ForceReply|null $replyMarkup = null,
        public bool $disableNotification = false,
        public ?int $messageThreadId = null,
        public ?bool $protectContent = null,
        public ?bool $allowSendingWithoutReply = null,
    ) {}
}
