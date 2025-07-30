<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Query\User\FindUserByTelegramUserId;

use App\HealthTracker\Application\DTO\UserData;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Query\QueryHandlerInterface;

final readonly class FindUserByTelegramUserIdQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
    ) {}

    public function __invoke(FindUserByTelegramUserIdQuery $query): ?UserData
    {
        $user = $this->userRepository->findByTelegramUserId(
            new TelegramUserId($query->telegramUserId)
        );

        return $user
            ? UserData::fromEntity($user)
            : null;
    }
}
