<?php

namespace App\Tenancy\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class TenantNotFoundException extends HttpException
{
    public function __construct(string $domain = '', ?\Throwable $previous = null)
    {
        $message = $domain ? "Tenant / Domain Not Found for [{$domain}]." : "Tenant / Domain Not Found.";
        parent::__construct(404, $message, $previous);
    }
}
