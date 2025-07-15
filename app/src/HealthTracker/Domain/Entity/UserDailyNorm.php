<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use App\HealthTracker\Domain\ValueObject\Shared\StepsAmount;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "user_daily_norms")]
class UserDailyNorm
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'userDailyNorm')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) User $user;

    #[ORM\Embedded(class: Macronutrients::class, columnPrefix: false)]
    public Macronutrients $macronutrients {
        get => $this->macronutrients;
        set => $this->macronutrients = $value;
    }

    #[ORM\Embedded(class: StepsAmount::class, columnPrefix: false)]
    public StepsAmount $steps {
        get => $this->steps;
        set => $this->steps = $value;
    }

    public function __construct(
        User $user,
        Macronutrients $macronutrients,
        StepsAmount $steps,
    )
    {
        $this->user = $user;
        $this->macronutrients = $macronutrients;
        $this->steps = $steps;
    }
}
