<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Shared;

use App\Shared\Domain\ValueObject\AbstractStringValueObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class Name extends AbstractStringValueObject
{
    #[ORM\Column(name: 'name', type: Types::STRING, length: 255)]
    protected string $value;
}
