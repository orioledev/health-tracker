<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Entity\Walk;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Domain\Repository\WalkRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use DateMalformedStringException;
use DateTime;
use DateTimeInterface;
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
     * @param DateTimeInterface $date
     * @return Walk[]
     * @throws DateMalformedStringException
     */
    public function findWalksByDate(User $user, DateTimeInterface $date): array
    {
        $startOfDay = DateTime::createFromInterface($date);
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this
            ->createQueryBuilder('w')
            ->where('w.user = :user')
            ->andWhere('w.createdAt >= :startOfDay')
            ->andWhere('w.createdAt < :endOfDay')
            ->addOrderBy('w.createdAt', 'ASC')
            ->addOrderBy('w.id', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @return int
     * @throws DateMalformedStringException
     */
    public function getTotalStepsByDate(User $user, DateTimeInterface $date): int
    {
        $startOfDay = DateTime::createFromInterface($date);
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

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @param Direction $direction
     * @return DateTimeInterface|null
     * @throws DateMalformedStringException
     */
    public function getDateWithWalks(User $user, DateTimeInterface $date, Direction $direction): ?DateTimeInterface
    {
        $startOfDay = DateTime::createFromInterface($date);
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        $qb = $this->createQueryBuilder('w');

        $qb->select('w.createdAt')
            ->where('w.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        if ($direction === Direction::PREV) {
            $qb->andWhere('w.createdAt < :startOfDay')
                ->orderBy('w.createdAt', 'DESC')
                ->setParameter('startOfDay', $startOfDay);
        } else {
            $qb->andWhere('w.createdAt > :endOfDay')
                ->orderBy('w.createdAt', 'ASC')
                ->setParameter('endOfDay', $endOfDay);
        }

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            return null;
        }

        return $result['createdAt']->setTime(0, 0, 0);
    }

    public function save(Walk $walk): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($walk);
        $entityManager->flush();
    }
}
