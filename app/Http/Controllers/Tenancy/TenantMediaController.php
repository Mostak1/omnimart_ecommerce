<?php

namespace App\Http\Controllers\Tenancy;

use App\Http\Controllers\Controller;
use App\Tenancy\TenantContext;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TenantMediaController extends Controller
{
    /**
     * Serve a file explicitly requested under /storage/tenants/{tenantId}/{path}.
     * Enforces tenant boundary: store1 cannot access store2 files!
     */
    public function serveTenantFile(Request $request, string $tenantId, string $path): BinaryFileResponse
    {
        $context = app(TenantContext::class);

        // Security check: if in a tenant request, forbid accessing other tenant's files
        if ($context->hasTenant() && $context->id() !== $tenantId) {
            abort(403, 'Cross-tenant file access is forbidden.');
        }

        $baseDir = storage_path('app/public/tenants/' . $tenantId);
        $realBaseDir = realpath($baseDir);

        if (!$realBaseDir) {
            abort(404);
        }

        $targetFile = $baseDir . '/' . ltrim($path, '/');
        $realTargetFile = realpath($targetFile);

        // Path traversal protection
        if (!$realTargetFile || !str_starts_with($realTargetFile, $realBaseDir) || !is_file($realTargetFile)) {
            abort(404);
        }

        return response()->file($realTargetFile, [
            'Cache-Control' => 'public, max-age=31536000',
        ]);
    }

    /**
     * Fallback handler for standard /storage/{path} requests in a tenant context.
     * Looks in the current tenant's isolated directory first, then falls back to public storage.
     */
    public function serveFallback(Request $request, string $path): BinaryFileResponse
    {
        $context = app(TenantContext::class);

        // 1. If inside tenant context, check tenant's isolated folder first
        if ($context->hasTenant()) {
            $tenantId = $context->id();
            $tenantBaseDir = realpath(storage_path('app/public/tenants/' . $tenantId));
            $tenantFile = realpath(storage_path('app/public/tenants/' . $tenantId . '/' . ltrim($path, '/')));

            if ($tenantBaseDir && $tenantFile && str_starts_with($tenantFile, $tenantBaseDir) && is_file($tenantFile)) {
                return response()->file($tenantFile, [
                    'Cache-Control' => 'public, max-age=31536000',
                ]);
            }
        }

        // 2. Fall back to shared public storage (e.g. system default placeholders)
        $publicBaseDir = realpath(storage_path('app/public'));
        $publicFile = realpath(storage_path('app/public/' . ltrim($path, '/')));

        // Ensure file is within public storage and NOT inside a tenant subdirectory if trying to guess
        if ($publicBaseDir && $publicFile && str_starts_with($publicFile, $publicBaseDir) && is_file($publicFile)) {
            // Prevent guessing files inside other tenants' folders via /storage/tenants/other_tenant/...
            $tenantsDir = realpath(storage_path('app/public/tenants'));
            if ($tenantsDir && str_starts_with($publicFile, $tenantsDir)) {
                // If in tenant directory, verify it belongs to current tenant
                if (!$context->hasTenant() || !str_starts_with($publicFile, storage_path('app/public/tenants/' . $context->id()))) {
                    abort(403, 'Cross-tenant file access is forbidden.');
                }
            }

            return response()->file($publicFile, [
                'Cache-Control' => 'public, max-age=31536000',
            ]);
        }

        abort(404);
    }
}
