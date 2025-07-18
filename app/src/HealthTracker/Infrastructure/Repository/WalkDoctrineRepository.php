<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use DateMalformedStringException;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Walk>
 */
class WalkDoctrineRepository extends ServiceEntityRepository implements WalkRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Walk::class);
    }

    public function findById(WalkId $walkId): ?Walk
    {
        return $this->find($walkId);
    }

    /**
     * @param User $user
     * @return int
     * @throws DateMalformedStringException
     */
    public function getTotalStepsToday(User $user): int
    {
        $today = new DateTime();
        $today->setTime(0, 0);

        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $result = $this
            ->createQueryBuilder('w')
            ->select('SUM(w.steps.value) as total_steps')
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= :today')
            ->andWhere('w.createdAt < :tomorrow')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)($result ?? 0);
    }

    public function save(Walk $walk): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($walk);
        $entityManager->flush();
    }
}
