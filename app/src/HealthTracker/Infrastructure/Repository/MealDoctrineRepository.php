<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\Entity\User;
use App\HealthTracker\Domain\Repository\MealRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use DateMalformedStringException;
use DateTime;
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
     * @param DateTime $date
     * @return Macronutrients
     * @throws DateMalformedStringException
     */
    public function getTotalMacronutrientsByDate(User $user, DateTime $date): Macronutrients
    {
        $startOfDay = clone $date;
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
            $result['total_calories'],
            $result['total_proteins'],
            $result['total_fats'],
            $result['total_carbohydrates'],
        );
    }

    /**
     * @param User $user
     * @return Macronutrients
     * @throws DateMalformedStringException
     */
    public function getTotalMacronutrientsToday(User $user): Macronutrients
    {
        return $this->getTotalMacronutrientsByDate($user, new DateTime());
    }

    public function save(Meal $meal): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($meal);
        $entityManager->flush();
    }
}
