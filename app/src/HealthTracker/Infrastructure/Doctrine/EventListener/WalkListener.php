<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\EventListener;

use App\HealthTracker\Domain\Calculator\WalkCaloriesAmount\WalkCaloriesAmountCalculatorInterface;
use App\HealthTracker\Domain\Entity\Walk;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;
use ReflectionNamedType;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: Walk::class)]
final readonly class WalkListener
{
    public function __construct(
        private WalkCaloriesAmountCalculatorInterface $walkCaloriesAmountCalculator,
    ) {}

    public function postLoad(Walk $walk, PostLoadEventArgs $event): void
    {
        $reflect = new ReflectionClass($walk);

        foreach ($reflect->getProperties() as $property) {
            $type = $property->getType();

            if (is_null($type) || $property->isInitialized($walk)) {
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                if ($type->getName() === WalkCaloriesAmountCalculatorInterface::class) {
                    $property->setValue($walk, $this->walkCaloriesAmountCalculator);
                    break;
                }
            }
        }
    }
}
