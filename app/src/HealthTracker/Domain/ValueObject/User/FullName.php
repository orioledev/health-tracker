<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\ValueObject\User;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final readonly class FullName
{
    public function __construct(
        #[ORM\Column(name: 'first_name', type: Types::STRING, length: 255)]
        protected string $firstName,
        #[ORM\Column(name: 'last_name', type: Types::STRING, length: 255, nullable: true)]
        protected ?string $lastName = null,
    ) {}

    final public function value(): string
    {
        if ($this->lastName === null) {
            return $this->firstName;
        }

        return $this->firstName . ' ' . $this->lastName;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): ?string
    {
        return $this->lastName;
    }
}
