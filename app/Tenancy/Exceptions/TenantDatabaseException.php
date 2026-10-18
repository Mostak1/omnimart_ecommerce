<?php

namespace App\Tenancy\Exceptions;

use RuntimeException;

class TenantDatabaseException extends RuntimeException
{
    public function __construct(string $message, int $code = 500, ?\Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
