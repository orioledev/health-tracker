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
     * @param DateTime $date
     * @return int
     * @throws DateMalformedStringException
     */
    public function getTotalStepsByDate(User $user, DateTime $date): int
    {
        $startOfDay = clone $date;
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        $result = $this
            ->createQueryBuilder('w')
            ->select('SUM(w.steps.value) as total_steps')
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= :startOfDay')
            ->andWhere('w.createdAt < :endOfDay')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleScalarResult();

        return (int)($result ?? 0);
    }

    /**
     * @param User $user
     * @return int
     * @throws DateMalformedStringException
     */
    public function getTotalStepsToday(User $user): int
    {
        return $this->getTotalStepsByDate($user, new DateTime());
    }

    public function save(Walk $walk): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($walk);
        $entityManager->flush();
    }
}
