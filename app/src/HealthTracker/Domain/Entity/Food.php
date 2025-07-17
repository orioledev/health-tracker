<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\ValueObject\Food\ExternalId;
use App\HealthTracker\Domain\ValueObject\Food\FoodId;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\Name;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "foods")]
#[ORM\UniqueConstraint(name: 'ux__foods__external_id', columns: ['external_id'])]
#[ORM\UniqueConstraint(name: 'ux__foods__name', columns: ['name'])]
class Food
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'food_id')]
    private(set) ?FoodId $id = null;

    #[ORM\Embedded(class: ExternalId::class, columnPrefix: false)]
    private(set) ExternalId $externalId;

    #[ORM\Embedded(class: Name::class, columnPrefix: false)]
    private(set) Name $name;

    #[ORM\Embedded(class: Macronutrients::class, columnPrefix: false)]
    private(set) Macronutrients $macronutrients;

    public function __construct(
        ExternalId $externalId,
        Name $name,
        Macronutrients $macronutrients,
    )
    {
        $this->externalId = $externalId;
        $this->name = $name;
        $this->macronutrients = $macronutrients;
    }
}
