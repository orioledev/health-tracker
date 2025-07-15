<?php

declare(strict_types=1);

namespace App\HealthTracker\Infrastructure\Doctrine\EventListener;

use App\HealthTracker\Domain\Calculator\BodyMassIndex\BodyMassIndexCalculatorInterface;
use App\HealthTracker\Domain\Entity\UserIndicator;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostLoadEventArgs;
use Doctrine\ORM\Events;
use ReflectionClass;
use ReflectionNamedType;

#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: UserIndicator::class)]
final readonly class UserIndicatorListener
{
    public function __construct(
        private BodyMassIndexCalculatorInterface $bodyMassIndexCalculator,
    ) {}

    public function postLoad(UserIndicator $userIndicator, PostLoadEventArgs $event): void
    {
        $reflect = new ReflectionClass($userIndicator);

        foreach ($reflect->getProperties() as $property) {
            $type = $property->getType();

            if (is_null($type) || $property->isInitialized($userIndicator)) {
                continue;
            }

            if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                if ($type->getName() === BodyMassIndexCalculatorInterface::class) {
                    $property->setValue($userIndicator, $this->bodyMassIndexCalculator);
                    break;
                }
            }
        }
    }
}
