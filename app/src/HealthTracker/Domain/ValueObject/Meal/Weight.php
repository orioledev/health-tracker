<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Meal;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Weight extends AbstractPositiveIntValueObject
{
    #[ORM\Column(name: 'weight', type: Types::SMALLINT)]
    protected int $value;
}
