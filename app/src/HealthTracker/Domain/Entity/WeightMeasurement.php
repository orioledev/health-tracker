<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\WeightMeasurement\WeightMeasurementId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "weight_measurements")]
#[ORM\Index(name: 'ix__weight_measurements__weight', columns: ['weight'])]
#[ORM\Index(name: 'ix__weight_measurements__created_at', columns: ['created_at'])]
class WeightMeasurement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'weight_measurement_id')]
    private(set) ?WeightMeasurementId $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'weightMeasurements')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) User $user;

    #[ORM\Column(type: 'weight', precision: 5, scale: 2)]
    private(set) Weight $weight;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        Weight $weight,
    )
    {
        $this->user = $user;
        $this->weight = $weight;
        $this->createdAt = new DateTimeImmutable();
    }
}
