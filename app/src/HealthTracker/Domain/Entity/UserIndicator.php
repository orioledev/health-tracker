<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Entity;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Enum\ActivityLevel;
use App\HealthTracker\Domain\Enum\WeightTargetType;
use App\HealthTracker\Domain\ValueObject\Shared\Weight;
use App\HealthTracker\Domain\ValueObject\UserIndicator\Height;
use Doctrine\Common\Collections\Criteria;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "user_indicators")]
class UserIndicator
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'indicator')]
    #[ORM\JoinColumn(nullable: false, onDelete: 'CASCADE')]
    private(set) User $user;

    #[ORM\Column(type: 'height', nullable: true)]
    public ?Height $height = null {
        get => $this->height;
        set => $this->height = $value;
    }

    #[ORM\Column(type: 'weight', precision: 5, scale: 2, nullable: true)]
    public ?Weight $initialWeight = null {
        get => $this->initialWeight;
        set {
            $this->initialWeight = $value;

            if ($this->initialWeight !== null && $this->targetWeight !== null) {
                $diff = round($this->targetWeight->value() - $this->initialWeight->value(), 1);
                $this->weightTargetType = WeightTargetType::getWeightTargetTypeByWeightDiff($diff);
            }
        }
    }

    #[ORM\Column(type: 'weight', precision: 5, scale: 2, nullable: true)]
    public ?Weight $targetWeight = null {
        get => $this->targetWeight;
        set {
            $this->targetWeight = $value;

            if ($this->initialWeight !== null && $this->targetWeight !== null) {
                $diff = round($this->targetWeight->value() - $this->initialWeight->value(), 1);
                $this->weightTargetType = WeightTargetType::getWeightTargetTypeByWeightDiff($diff);
            }
        }
    }

    #[ORM\Column(name: 'activity_level', type: Types::SMALLINT, nullable: true, enumType: ActivityLevel::class)]
    public ?ActivityLevel $activityLevel = null {
        get => $this->activityLevel;
        set => $this->activityLevel = $value;
    }

    #[ORM\Column(name: 'weight_target_type', type: Types::SMALLINT, nullable: true, enumType: WeightTargetType::class)]
    private(set) ?WeightTargetType $weightTargetType = null {
        get => $this->weightTargetType;
    }

    private(set) ?Weight $currentWeight = null {
        get {
            if ($this->currentWeight !== null) {
                return $this->currentWeight;
            }

            $criteria = Criteria::create()->setMaxResults(1);
            $lastWeightMeasurement = $this->user->weightMeasurements->matching($criteria)->first() ?: null;

            $this->currentWeight = $lastWeightMeasurement
                ? $lastWeightMeasurement->weight
                : $this->initialWeight;

            return $this->currentWeight;
        }
    }

    public function __construct(
        User $user,
        private readonly BodyMassIndexCalculatorInterface $bodyMassIndexCalculator,
    )
    {
        $this->user = $user;
    }

    public function isFilled(): bool
    {
        return $this->height !== null
            && $this->initialWeight !== null
            && $this->targetWeight !== null
            && $this->activityLevel !== null
            && $this->weightTargetType !== null;
    }

    public function getInitialBmi(): ?float
    {
        if ($this->initialWeight === null || $this->height === null) {
            return null;
        }

        return $this->calculateBmi($this->initialWeight, $this->height);
    }

    public function getCurrentBmi(): ?float
    {
        if ($this->currentWeight === null || $this->height === null) {
            return null;
        }

        return $this->calculateBmi($this->currentWeight, $this->height);
    }

    private function calculateBmi(Weight $weight, Height $height): ?float
    {
        return $this->bodyMassIndexCalculator->calculate($weight, $height);
    }
}
