<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\Food;

use App\Shared\Domain\ValueObject\AbstractStringValueObject;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class ExternalId extends AbstractStringValueObject
{
    protected const int MIN_LENGTH = 1;
    protected const int MAX_LENGTH = 64;

    #[ORM\Column(name: 'external_id', type: Types::STRING, length: 64)]
    protected string $value;
}
