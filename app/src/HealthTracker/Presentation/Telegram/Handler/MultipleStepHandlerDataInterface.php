<?php

declare(strict_types=1);

namespace App\HealthTracker\Presentation\Telegram\Handler;

interface MultipleStepHandlerDataInterface
{
    public function toArray(): array;
}
