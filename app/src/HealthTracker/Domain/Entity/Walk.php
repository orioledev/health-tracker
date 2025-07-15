<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorArgs;
use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorInterface;
use App\HealthTracker\Domain\ValueObject\Shared\CaloriesAmount;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use App\HealthTracker\Domain\ValueObject\Walk\WalkId;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "walks")]
#[ORM\Index(name: 'ix__walks__steps', columns: ['steps'])]
#[ORM\Index(name: 'ix__walks__calories', columns: ['calories'])]
#[ORM\Index(name: 'ix__walks__created_at', columns: ['created_at'])]
class Walk
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'walk_id')]
    private(set) ?WalkId $id = null;

    #[ORM\ManyToOne(targetEntity: User::class)]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) User $user;

    #[ORM\Embedded(class: StepsAmount::class, columnPrefix: false)]
    private(set) StepsAmount $stepsAmount;

    #[ORM\Embedded(class: CaloriesAmount::class, columnPrefix: false)]
    private(set) CaloriesAmount $caloriesAmount;

    #[ORM\Column(type: Types::DATETIME_IMMUTABLE)]
    private(set) DateTimeImmutable $createdAt;

    public function __construct(
        User $user,
        StepsAmount $stepsAmount,
        WalkCaloriesAmountCalculatorInterface $walkCaloriesAmountCalculator,
    )
    {
        $this->user = $user;
        $this->stepsAmount = $stepsAmount;
        $this->createdAt = new DateTimeImmutable();

        $this->caloriesAmount = $walkCaloriesAmountCalculator->calculate(
            WalkCaloriesAmountCalculatorArgs::fromEntity($this->user),
            $this->stepsAmount
        );
    }
}
