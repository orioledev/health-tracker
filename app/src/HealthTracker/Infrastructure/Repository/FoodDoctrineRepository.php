<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\Food;
use App\HealthTracker\Domain\Repository\FoodRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\HealthTracker\Domain\ValueObject\Food\FoodId;
use App\HealthTracker\Domain\ValueObject\Shared\Name;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<Food>
 */
class FoodDoctrineRepository extends ServiceEntityRepository implements FoodRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Food::class);
    }

    public function findById(FoodId $foodId): ?Food
    {
        return $this->find($foodId);
    }

    public function findByExternalId(ExternalId $externalId): ?Food
    {
        return $this->findOneBy(['externalId.value' => $externalId->value()]);
    }

    public function findByName(Name $name): ?Food
    {
        return $this
            ->createQueryBuilder('f')
            ->where('LOWER(f.name.value) LIKE LOWER(:name)')
            ->setParameter('name', $name->value() . '%')
            ->getQuery()
            ->getOneOrNullResult();
    }


    public function save(Food $food): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($food);
        $entityManager->flush();
    }
}
