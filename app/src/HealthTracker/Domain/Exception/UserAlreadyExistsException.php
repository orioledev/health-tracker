<?php

declare(strict_types=1);

namespace App\HealthTracker\Domain\Exception;

use DomainException;

class UserAlreadyExistsException extends DomainException {}
