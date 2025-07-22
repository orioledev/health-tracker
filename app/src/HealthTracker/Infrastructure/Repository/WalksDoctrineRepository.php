<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Entity\WeightMeasurement;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\Repository\WeightMeasurementRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Walk>
 */
class WalksDoctrineRepository extends ServiceEntityRepository implements WalkRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Walk::class);
    }

    public function findById(WalkId $walkId): ?Walk
    {
        return $this->find($walkId);
    }

    public function findPrevDayWalk(Walk $walk): ?Walk
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.createdAt < :createdAt')
            ->andWhere('w.id != :currentId')
            ->setParameter('user', $walk->user)
            ->setParameter('createdAt', $walk->createdAt)
            ->setParameter('currentId', $walk->id)
            ->orderBy('w.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function save(Walk $walk): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($walk);
        $entityManager->flush();
    }

    public function getTotalStepsToday(User $user): int
    {
        return $this->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= now()::date')
//            ->andWhere('w.id != :currentId')
            ->setParameter('user', $user->id)
//            ->setParameter('createdAt', $walk->createdAt)
//            ->setParameter('currentId', $walk->id)
            ->orderBy('w.createdAt', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
