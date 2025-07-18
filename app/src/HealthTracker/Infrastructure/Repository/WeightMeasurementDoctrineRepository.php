<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Repository;

use App\HealthTracker\Domain\Entity\WeightMeasurement;
use App\HealthTracker\Domain\Repository\WeightMeasurementRepositoryInterface;
use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @template-extends ServiceEntityRepository<WeightMeasurement>
 */
class WeightMeasurementDoctrineRepository extends ServiceEntityRepository implements WeightMeasurementRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, WeightMeasurement::class);
    }

    public function findById(WeightMeasurementId $weightMeasurementId): ?WeightMeasurement
    {
        return $this->find($weightMeasurementId);
    }

    public function save(WeightMeasurement $weightMeasurement): void
    {
        $entityManager = $this->getEntityManager();

        $entityManager->persist($weightMeasurement);
        $entityManager->flush();
    }
}
