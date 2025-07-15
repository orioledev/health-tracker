<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\WeightMeasurement;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class WeightMeasurementId extends AbstractPositiveIntValueObject
{
    #[ORM\Column(name: 'id', type: Types::BIGINT)]
    protected int $value;
}
