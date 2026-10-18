<?php

namespace App\Http\Middleware;

use App\Tenancy\Exceptions\TenantDisabledException;
use App\Tenancy\Exceptions\TenantNotFoundException;
use App\Tenancy\TenantManager;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IdentifyTenant
{
    protected TenantManager $manager;

    public function __construct(TenantManager $manager)
    {
        $this->manager = $manager;
    }

    /**
     * Handle an incoming request.
     *
     * Identifies the tenant from the request host as early as possible in the
     * HTTP request lifecycle, switches the database connection, isolates cache,
     * session, and files, and locks the tenant context.
     *
     * If the domain is unknown, fails safely without touching the default database.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!config('tenancy.enabled', true)) {
            return $next($request);
        }

        $host = $request->getHost();
        $resolver = $this->manager->getResolver();
        $normalizedHost = $resolver->normalizeHost($host);

        // 1. Check if this is a central/system domain
        if ($resolver->isCentralDomain($normalizedHost)) {
            $this->manager->getContext()->markAsCentral(
                lock: config('tenancy.lock_context', true)
            );

            return $next($request);
        }

        // 2. Resolve the tenant from domain mapping
        $tenant = $resolver->resolveFromHost($normalizedHost);

        // 3. Fail safely if domain is not registered - NEVER fall back to default database
        if (!$tenant) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'error' => 'Tenant / Domain Not Found',
                    'message' => "The domain [{$normalizedHost}] is not registered to any store.",
                ], 404);
            }

            return response(
                view()->exists('errors.tenant-not-found')
                    ? view('errors.tenant-not-found', ['domain' => $normalizedHost])->render()
                    : "<!DOCTYPE html><html><head><title>Tenant Not Found</title></head><body style='font-family:sans-serif;padding:50px;text-align:center;'><h1>404 - Tenant / Domain Not Found</h1><p>The domain <strong>" . e($normalizedHost) . "</strong> is not configured for any store.</p></body></html>",
                404,
                ['Content-Type' => 'text/html']
            );
        }

        // 4. Verify tenant active status
        if (!$tenant->isActive()) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => false,
                    'error' => 'Tenant Disabled',
                    'message' => "The store for [{$normalizedHost}] is currently disabled.",
                ], 403);
            }

            return response(
                view()->exists('errors.tenant-disabled')
                    ? view('errors.tenant-disabled', ['domain' => $normalizedHost])->render()
                    : "<!DOCTYPE html><html><head><title>Tenant Disabled</title></head><body style='font-family:sans-serif;padding:50px;text-align:center;'><h1>403 - Tenant Disabled</h1><p>The store for <strong>" . e($normalizedHost) . "</strong> is currently disabled.</p></body></html>",
                403,
                ['Content-Type' => 'text/html']
            );
        }

        // 5. Bootstrap tenant: switches DB, sets default connection, locks context, isolates cache/session/storage
        $this->manager->bootstrapTenant($tenant, lock: config('tenancy.lock_context', true));

        // 6. Execute application with tenant locked
        return $next($request);
    }

    /**
     * Terminate request lifecycle.
     */
    public function terminate(Request $request, Response $response): void
    {
        // For long-running worker environments like Octane/RoadRunner/Swoole,
        // ensure context and connections are reset after the request completes.
        if (isset($_SERVER['LARAVEL_OCTANE'])) {
            $this->manager->reset();
        }
    }
}
