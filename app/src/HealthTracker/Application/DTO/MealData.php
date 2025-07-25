<?php

declare(strict_types=1);

namespace App\HealthTracker\Application\DTO;

use App\HealthTracker\Domain\Entity\Meal;
use App\HealthTracker\Domain\ValueObject\Shared\Macronutrients;
use DateTimeInterface;

final readonly class MealData
{
    public function __construct(
        public string $name,
        public int $weight,
        public Macronutrients $macronutrients,
        public DateTimeInterface $createdAt,
    ) {}

    public static function fromEntity(Meal $meal): self
    {
        return new self(
            name: $meal->name->value(),
            weight: $meal->weight->value(),
            macronutrients: $meal->macronutrients,
            createdAt: $meal->createdAt,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'weight' => $this->weight,
            'macronutrients' => $this->macronutrients->toArray(),
            'createdAt' => $this->createdAt,
        ];
    }
}
