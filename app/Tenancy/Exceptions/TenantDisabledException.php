<?php

namespace App\Tenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantDisabledException extends HttpException
{
    public function __construct(string $tenantId = '', ?\Throwable $previous = null)
    {
        $message = $tenantId ? "Tenant [{$tenantId}] is currently disabled or inactive." : "Tenant is currently disabled.";
        parent::__construct(403, $message, $previous);
    }
}
