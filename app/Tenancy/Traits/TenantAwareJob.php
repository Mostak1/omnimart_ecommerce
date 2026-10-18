<?php

namespace App\Tenancy\Traits;

use App\Tenancy\TenantContext;
use App\Tenancy\TenantManager;

trait TenantAwareJob
{
    /**
     * The tenant ID this job belongs to.
     */
    public ?string $tenantId = null;

    /**
     * Bind current tenant context when instantiating the job.
     */
    public function initializeTenantAwareJob(): void
    {
        if (app()->bound(TenantContext::class)) {
            $context = app(TenantContext::class);
            if ($context->hasTenant()) {
                $this->tenantId = $context->id();
            }
        }
    }

    /**
     * Ensure the tenant context is bootstrapped before the job handles.
     */
    public function bootstrapJobTenant(): void
    {
        if ($this->tenantId && app()->bound(TenantManager::class)) {
            $manager = app(TenantManager::class);
            $context = $manager->getContext();

            if (!$context->hasTenant() || $context->id() !== $this->tenantId) {
                $manager->bootstrapTenantById($this->tenantId, lock: false);
            }
        }
    }

    /**
     * Reset the tenant context after the job finishes.
     */
    public function resetJobTenant(): void
    {
        if (app()->bound(TenantManager::class)) {
            app(TenantManager::class)->reset();
        }
    }
}
