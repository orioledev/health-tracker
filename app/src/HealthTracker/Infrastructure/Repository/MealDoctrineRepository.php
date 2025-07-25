<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Enum\Direction;
use App\HealthTracker\Domain\Repository\MealRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use DateMalformedStringException;
use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Meal>
 */
class MealDoctrineRepository extends ServiceEntityRepository implements MealRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Meal::class);
    }

    public function findById(MealId $mealId): ?Meal
    {
        return $this->find($mealId);
    }

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @return Meal[]
     * @throws DateMalformedStringException
     */
    public function findMealsByDate(User $user, DateTimeInterface $date): array
    {
        $startOfDay = DateTime::createFromInterface($date);
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        return $this
            ->createQueryBuilder('m')
            ->where('m.user = :user')
            ->andWhere('m.createdAt >= :startOfDay')
            ->andWhere('m.createdAt < :endOfDay')
            ->addOrderBy('m.createdAt', 'ASC')
            ->addOrderBy('m.id', 'ASC')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @return Macronutrients
     * @throws DateMalformedStringException
     */
    public function getTotalMacronutrientsByDate(User $user, DateTimeInterface $date): Macronutrients
    {
        $startOfDay = DateTime::createFromInterface($date);
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        $result = $this
            ->createQueryBuilder('m')
            ->select(
                'SUM(m.macronutrients.calories) as total_calories',
                'SUM(m.macronutrients.proteins) as total_proteins',
                'SUM(m.macronutrients.fats) as total_fats',
                'SUM(m.macronutrients.carbohydrates) as total_carbohydrates',
            )
            ->where('m.user = :user')
            ->andWhere('m.createdAt >= :startOfDay')
            ->andWhere('m.createdAt < :endOfDay')
            ->setParameter('user', $user)
            ->setParameter('startOfDay', $startOfDay)
            ->setParameter('endOfDay', $endOfDay)
            ->getQuery()
            ->getSingleResult();

        return new Macronutrients(
            $result['total_calories'] ?: 0,
            $result['total_proteins'] ?: 0,
            $result['total_fats'] ?: 0,
            $result['total_carbohydrates'] ?: 0,
        );
    }

    /**
     * @param User $user
     * @return Macronutrients
     * @throws DateMalformedStringException
     */
    public function getTotalMacronutrientsToday(User $user): Macronutrients
    {
        return $this->getTotalMacronutrientsByDate($user, new DateTimeImmutable());
    }

    /**
     * @param User $user
     * @param DateTimeInterface $date
     * @param Direction $direction
     * @return DateTimeInterface|null
     * @throws DateMalformedStringException
     */
    public function getDateWithMeals(User $user, DateTimeInterface $date, Direction $direction): ?DateTimeInterface
    {
        $startOfDay = DateTime::createFromInterface($date);
        $startOfDay->setTime(0, 0);

        $endOfDay = clone $startOfDay;
        $endOfDay->modify('+1 day');

        $qb = $this->createQueryBuilder('m');

        $qb->select('m.createdAt')
            ->where('m.user = :user')
            ->setParameter('user', $user)
            ->setMaxResults(1);

        if ($direction === Direction::PREV) {
            $qb->andWhere('m.createdAt < :startOfDay')
                ->orderBy('m.createdAt', 'DESC')
                ->setParameter('startOfDay', $startOfDay);
        } else {
            $qb->andWhere('m.createdAt > :endOfDay')
                ->orderBy('m.createdAt', 'ASC')
                ->setParameter('endOfDay', $endOfDay);
        }

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            return null;
        }

        return $result['createdAt']->setTime(0, 0, 0);
    }

    public function save(Meal $meal): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($meal);
        $entityManager->flush();
    }
}
