<?php

declare(strict_types=1);

namespace App\Shared\Domain\ValueObject;

use Stringable;

abstract readonly class AbstractValueObject implements Stringable
{
    abstract public function value(): mixed;

    public function __toString(): string
    {
        return (string)$this->value();
    }
}
