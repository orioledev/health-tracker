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
     * @return Macronutrients
     * @throws DateMalformedStringException
     */
    public function getTotalMacronutrientsToday(User $user): Macronutrients
    {
        $today = new DateTime();
        $today->setTime(0, 0);

        $tomorrow = clone $today;
        $tomorrow->modify('+1 day');

        $result = $this
            ->createQueryBuilder('m')
            ->select(
                'SUM(m.macronutrients.calories) as total_calories',
                'SUM(m.macronutrients.proteins) as total_proteins',
                'SUM(m.macronutrients.fats) as total_fats',
                'SUM(m.macronutrients.carbohydrates) as total_carbohydrates',
            )
            ->where('m.user = :user')
            ->andWhere('m.createdAt >= :today')
            ->andWhere('m.createdAt < :tomorrow')
            ->setParameter('user', $user)
            ->setParameter('today', $today)
            ->setParameter('tomorrow', $tomorrow)
            ->getQuery()
            ->getSingleResult();

        return new Macronutrients(
            $result['total_calories'],
            $result['total_proteins'],
            $result['total_fats'],
            $result['total_carbohydrates'],
        );
    }

    public function save(Meal $meal): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($meal);
        $entityManager->flush();
    }
}
