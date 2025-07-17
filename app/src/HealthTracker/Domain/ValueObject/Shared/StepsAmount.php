<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Shared;

use App\Shared\Domain\ValueObject\AbstractPositiveIntValueObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class StepsAmount extends AbstractPositiveIntValueObject
{
    #[ORM\Column(name: 'steps', type: Types::INTEGER)]
    protected int $value;
}
