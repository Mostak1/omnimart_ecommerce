<?php

namespace App\Tenancy\Exceptions;

use RuntimeException;

class TenantLockedException extends RuntimeException
{
    public function __construct(string $message = "Tenant context is locked and cannot be modified halfway through the request.", int $code = 423, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
