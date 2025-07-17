<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\ValueObject\Meal\MealId;
use App\HealthTracker\Domain\ValueObject\Meal\Weight;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\Name;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "meals")]
#[ORM\Index(name: 'ix__meals__calories', columns: ['calories'])]
#[ORM\Index(name: 'ix__meals__proteins', columns: ['proteins'])]
#[ORM\Index(name: 'ix__meals__fats', columns: ['fats'])]
#[ORM\Index(name: 'ix__meals__carbohydrates', columns: ['carbohydrates'])]
#[ORM\Index(name: 'ix__meals__created_at', columns: ['created_at'])]
class Meal
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'meal_id')]
    private(set) ?MealId $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) User $user;

    #[ORM\ManyToOne(targetEntity: Food::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) Food $food;

    #[ORM\Embedded(class: Name::class, columnPrefix: false)]
    private(set) Name $name;

    #[ORM\Embedded(class: Weight::class, columnPrefix: false)]
    private(set) Weight $weight;

    #[ORM\Embedded(class: Macronutrients::class, columnPrefix: false)]
    private(set) Macronutrients $macronutrients;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        Food $food,
        Name $name,
        Weight $weight,
    )
    {
        $this->user = $user;
        $this->food = $food;
        $this->name = $name;
        $this->weight = $weight;
        $this->createdAt = new DateTimeImmutable();

        $this->macronutrients = $food->macronutrients->perWeight($weight->value());
    }
}
