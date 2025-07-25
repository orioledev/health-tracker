<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Enum;

enum Direction: string
{
    case PREV = 'prev';
    case NEXT = 'next';
}
