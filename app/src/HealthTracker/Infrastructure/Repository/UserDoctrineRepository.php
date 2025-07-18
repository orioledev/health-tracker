<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Exception\UserNotFoundException;
use App\HealthTracker\Domain\Repository\UserRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\User\TelegramUserId;
use App\HealthTracker\Domain\ValueObject\User\UserId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<User>
 */
class UserDoctrineRepository extends ServiceEntityRepository implements UserRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function findById(UserId $userId): ?User
    {
        return $this->find($userId);
    }

    public function findByTelegramUserId(TelegramUserId $telegramUserId): ?User
    {
        return $this->findOneBy(['telegramUserId' => $telegramUserId->value()]);
    }

    /**
     * @param TelegramUserId $telegramUserId
     * @return User
     * @throws UserNotFoundException
     */
    public function findByTelegramUserIdOrFail(TelegramUserId $telegramUserId): User
    {
        $user = $this->findByTelegramUserId($telegramUserId);

        if (!$user) {
            throw new UserNotFoundException('Пользователь не найден');
        }

        return $this->findOneBy(['telegramUserId' => $telegramUserId->value()]);
    }

    public function existsByTelegramUserId(TelegramUserId $telegramUserId): bool
    {
        $count = $this->count(['telegramUserId' => $telegramUserId->value()]);

        return $count > 0;
    }

    public function save(User $user): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($user);
        $entityManager->flush();
    }
}
