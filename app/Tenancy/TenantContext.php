<?php

namespace App\Tenancy;

use App\Tenancy\Exceptions\TenantLockedException;

class TenantContext
{
    protected ?Tenant $tenant = null;
    protected bool $isLocked = false;
    protected bool $isCentral = false;

    /**
     * Set the current tenant.
     * Throws TenantLockedException if context is locked and a different tenant is assigned.
     */
    public function setTenant(Tenant $tenant, bool $lock = false): void
    {
        if ($this->isLocked && $this->tenant !== null && $this->tenant->id !== $tenant->id) {
            throw new TenantLockedException(
                "Cannot change tenant to [{$tenant->id}]. The current request is locked to tenant [{$this->tenant->id}]."
            );
        }

        $this->tenant = $tenant;
        $this->isCentral = false;

        if ($lock) {
            $this->isLocked = true;
        }
    }

    /**
     * Mark context as a central domain request.
     */
    public function markAsCentral(bool $lock = false): void
    {
        if ($this->isLocked && $this->tenant !== null) {
            throw new TenantLockedException(
                "Cannot switch context to central domain. Request is locked to tenant [{$this->tenant->id}]."
            );
        }

        $this->tenant = null;
        $this->isCentral = true;

        if ($lock) {
            $this->isLocked = true;
        }
    }

    /**
     * Get the active tenant.
     */
    public function getTenant(): ?Tenant
    {
        return $this->tenant;
    }

    /**
     * Get current tenant ID.
     */
    public function id(): ?string
    {
        return $this->tenant?->id;
    }

    /**
     * Check if a tenant is currently bound.
     */
    public function hasTenant(): bool
    {
        return $this->tenant !== null;
    }

    /**
     * Check if this request is running as a central domain.
     */
    public function isCentral(): bool
    {
        return $this->isCentral;
    }

    /**
     * Lock the context against further changes for this request.
     */
    public function lock(): void
    {
        $this->isLocked = true;
    }

    /**
     * Check if context is locked.
     */
    public function isLocked(): bool
    {
        return $this->isLocked;
    }

    /**
     * Unlock context (strictly used during worker/system reset between requests/jobs).
     */
    public function unlock(): void
    {
        $this->isLocked = false;
    }

    /**
     * Clear and unlock context.
     */
    public function clear(): void
    {
        $this->tenant = null;
        $this->isCentral = false;
        $this->isLocked = false;
    }
}
