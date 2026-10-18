<?php

namespace App\Tenancy;

use App\Tenancy\Exceptions\TenantDisabledException;
use App\Tenancy\Exceptions\TenantNotFoundException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class TenantManager
{
    protected TenantContext $context;
    protected TenantResolver $resolver;
    protected TenantDatabaseManager $databaseManager;

    protected ?string $originalCachePrefix = null;
    protected ?string $originalSessionCookie = null;
    protected ?string $originalStorageRoot = null;
    protected ?string $originalStorageUrl = null;

    public function __construct(
        TenantContext $context,
        TenantResolver $resolver,
        TenantDatabaseManager $databaseManager
    ) {
        $this->context = $context;
        $this->resolver = $resolver;
        $this->databaseManager = $databaseManager;
    }

    /**
     * Get the tenant context.
     */
    public function getContext(): TenantContext
    {
        return $this->context;
    }

    /**
     * Get the tenant resolver.
     */
    public function getResolver(): TenantResolver
    {
        return $this->resolver;
    }

    /**
     * Get the database manager.
     */
    public function getDatabaseManager(): TenantDatabaseManager
    {
        return $this->databaseManager;
    }

    /**
     * Bootstrap and activate a tenant:
     * 1. Validates status
     * 2. Sets and optionally locks TenantContext
     * 3. Switches and connects database
     * 4. Applies cache, session, and file storage isolation
     * 5. Injects tenant context into application logs
     */
    public function bootstrapTenant(Tenant $tenant, bool $lock = true): void
    {
        if (!$tenant->isActive()) {
            throw new TenantDisabledException($tenant->id);
        }

        // 1. Set tenant context (throws if locked to another tenant)
        $this->context->setTenant($tenant, $lock);

        // 2. Switch dynamic database connection
        $this->databaseManager->connect($tenant);

        // 3. Apply Cache Isolation
        $this->applyCacheIsolation($tenant);

        // 4. Apply Session Isolation
        $this->applySessionIsolation($tenant);

        // 5. Apply File Storage Isolation
        $this->applyStorageIsolation($tenant);

        // 6. Apply Logging Context
        $this->applyLoggingContext($tenant);
    }

    /**
     * Bootstrap tenant by host / domain.
     */
    public function bootstrapTenantByHost(string $host, bool $lock = true): ?Tenant
    {
        $tenant = $this->resolver->resolveFromHost($host);

        if (!$tenant) {
            throw new TenantNotFoundException($host);
        }

        $this->bootstrapTenant($tenant, $lock);

        return $tenant;
    }

    /**
     * Bootstrap tenant by tenant ID.
     */
    public function bootstrapTenantById(string $id, bool $lock = false): Tenant
    {
        $tenant = $this->resolver->resolveById($id);

        if (!$tenant) {
            throw new TenantNotFoundException("ID: {$id}");
        }

        $this->bootstrapTenant($tenant, $lock);

        return $tenant;
    }

    /**
     * Apply cache key prefix and directory isolation per tenant.
     */
    protected function applyCacheIsolation(Tenant $tenant): void
    {
        if (!config('tenancy.cache.prefix_by_tenant', true)) {
            return;
        }

        if ($this->originalCachePrefix === null) {
            $this->originalCachePrefix = config('cache.prefix');
        }

        $tenantPrefix = ($this->originalCachePrefix ?: 'omnimart_cache') . '_tenant_' . $tenant->id . '_';
        config(['cache.prefix' => $tenantPrefix]);

        if (config('tenancy.cache.isolate_file_store', true) && config('cache.default') === 'file') {
            $tenantCachePath = storage_path('framework/cache/data/tenants/' . $tenant->id);
            if (!is_dir($tenantCachePath)) {
                @mkdir($tenantCachePath, 0755, true);
            }
            config(['cache.stores.file.path' => $tenantCachePath]);
        }

        // Flush resolved cache drivers so new prefix/path takes effect immediately
        if (app()->bound('cache')) {
            app('cache')->forgetDriver();
        }
    }

    /**
     * Apply session cookie scoping and file storage per tenant.
     */
    protected function applySessionIsolation(Tenant $tenant): void
    {
        if (!config('tenancy.session.prefix_cookie_by_tenant', true)) {
            return;
        }

        if ($this->originalSessionCookie === null) {
            $this->originalSessionCookie = config('session.cookie');
        }

        $tenantCookie = ($this->originalSessionCookie ?: 'omnimart_session') . '_' . $tenant->id;
        config(['session.cookie' => $tenantCookie]);

        if (config('tenancy.session.isolate_file_store', true) && config('session.driver') === 'file') {
            $tenantSessionPath = storage_path('framework/sessions/tenants/' . $tenant->id);
            if (!is_dir($tenantSessionPath)) {
                @mkdir($tenantSessionPath, 0755, true);
            }
            config(['session.files' => $tenantSessionPath]);
        }
    }

    /**
     * Apply file storage disk path and URL isolation per tenant.
     */
    protected function applyStorageIsolation(Tenant $tenant): void
    {
        if (!config('tenancy.storage.isolate_public_disk', true)) {
            return;
        }

        if ($this->originalStorageRoot === null) {
            $this->originalStorageRoot = config('filesystems.disks.public.root');
            $this->originalStorageUrl = config('filesystems.disks.public.url');
        }

        $tenantStoragePath = storage_path('app/public/tenants/' . $tenant->id);
        if (!is_dir($tenantStoragePath)) {
            @mkdir($tenantStoragePath, 0755, true);
        }

        config(['filesystems.disks.public.root' => $tenantStoragePath]);
        config(['filesystems.disks.public.url' => rtrim(config('app.url'), '/') . '/storage/tenants/' . $tenant->id]);

        if (app()->bound('filesystem')) {
            Storage::forgetDisk('public');
        }
    }

    /**
     * Add tenant context to Laravel logs.
     */
    protected function applyLoggingContext(Tenant $tenant): void
    {
        if (!config('tenancy.logging.add_context', true)) {
            return;
        }

        if (method_exists(Log::class, 'shareContext')) {
            Log::shareContext([
                'tenant' => $tenant->id,
                'domain' => $tenant->domain,
                'database' => $tenant->database,
            ]);
        }
    }

    /**
     * Reset tenant state, disconnect database, and restore defaults.
     * Essential between queue worker jobs and long-running processes (Octane, Horizon, Supervisor).
     */
    public function reset(): void
    {
        // 1. Disconnect tenant database and restore central connection
        $this->databaseManager->disconnect();

        // 2. Restore cache
        if ($this->originalCachePrefix !== null) {
            config(['cache.prefix' => $this->originalCachePrefix]);
            if (config('cache.default') === 'file') {
                config(['cache.stores.file.path' => storage_path('framework/cache/data')]);
            }
            if (app()->bound('cache')) {
                app('cache')->forgetDriver();
            }
        }

        // 3. Restore session
        if ($this->originalSessionCookie !== null) {
            config(['session.cookie' => $this->originalSessionCookie]);
            if (config('session.driver') === 'file') {
                config(['session.files' => storage_path('framework/sessions')]);
            }
        }

        // 4. Restore storage
        if ($this->originalStorageRoot !== null) {
            config(['filesystems.disks.public.root' => $this->originalStorageRoot]);
            config(['filesystems.disks.public.url' => $this->originalStorageUrl]);
            if (app()->bound('filesystem')) {
                Storage::forgetDisk('public');
            }
        }

        // 5. Clear logging context
        if (method_exists(Log::class, 'shareContext')) {
            Log::shareContext([]);
        }

        // 6. Clear and unlock context
        $this->context->clear();
    }

    /**
     * Execute a callback scoped strictly to the specified tenant, then restore previous state.
     */
    public function runAsTenant(Tenant $tenant, callable $callback): mixed
    {
        $previousTenant = $this->context->getTenant();
        $previousLocked = $this->context->isLocked();

        // Unlock to permit transition
        $this->context->unlock();

        try {
            $this->bootstrapTenant($tenant, lock: false);
            return $callback($tenant);
        } finally {
            if ($previousTenant !== null) {
                $this->bootstrapTenant($previousTenant, lock: $previousLocked);
            } else {
                $this->reset();
            }
        }
    }
}
