<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\CheckUserExistenceByTelegramUserId;

use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Query\QueryHandlerInterface;

final readonly class CheckUserExistenceByTelegramUserIdQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(CheckUserExistenceByTelegramUserIdQuery $query): bool
    {
        return $this->userRepository->existsByTelegramUserId(
            new TelegramUserId($query->telegramUserId)
        );
    }
}
