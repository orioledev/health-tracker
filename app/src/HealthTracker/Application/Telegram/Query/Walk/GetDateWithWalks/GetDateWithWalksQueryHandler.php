<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\Telegram\Query\Walk\GetDateWithWalks;

use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\Shared\Application\Query\QueryHandlerInterface;
use DateTimeInterface;

final readonly class GetDateWithWalksQueryHandler implements QueryHandlerInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private WalkRepositoryInterface $walkRepository,
    ) {}

    public function __invoke(GetDateWithWalksQuery $query): ?DateTimeInterface
    {
        $user = $this->userRepository->findByTelegramUserIdOrFail(
            new TelegramUserId($query->telegramUserId)
        );

        return $this->walkRepository->getDateWithWalks($user, $query->date, $query->direction);
    }
}
