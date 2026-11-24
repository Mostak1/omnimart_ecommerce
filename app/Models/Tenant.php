<?php

namespace App\Models;

use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    /**
     * Check if tenant store is suspended.
     */
    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    /**
     * Get plan name.
     */
    public function getPlanNameAttribute(): string
    {
        return $this->attributes['plan_name'] ?? $this->attributes['tenancy_plan_name'] ?? 'Starter (ট্রায়াল)';
    }

    /**
     * Get plan expiration date.
     */
    public function getExpiresAtAttribute(): ?\Carbon\Carbon
    {
        $exp = $this->attributes['expires_at'] ?? $this->attributes['tenancy_expires_at'] ?? null;
        return $exp ? \Carbon\Carbon::parse($exp) : null;
    }

    /**
     * Check if plan has expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at ? $this->expires_at->isPast() : false;
    }

    /**
     * Days remaining until expiry.
     */
    public function daysRemaining(): int
    {
        if (!$this->expires_at) {
            return 999;
        }
        return max(0, (int)now()->diffInDays($this->expires_at, false));
    }

    /**
     * Custom columns explicitly stored in the tenants table.
     * All other columns will be automatically saved into the 'data' JSON column.
     *
     * @return array
     */
    public static function getCustomColumns(): array
    {
        return [
            'id',
            'shop_name',
            'owner_name',
            'phone',
            'address',
            'latitude',
            'longitude',
            'shop_type',
            'status',
        ];
    }
}

