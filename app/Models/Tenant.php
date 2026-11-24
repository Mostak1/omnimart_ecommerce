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
     * Relationship to ShopCommission (Wallet & Due Settings).
     */
    public function commission()
    {
        return $this->hasOne(ShopCommission::class, 'tenant_id', 'id');
    }

    /**
     * Relationship to ShopCommissionLog (Audit Logs).
     */
    public function commissionLogs()
    {
        return $this->hasMany(ShopCommissionLog::class, 'tenant_id', 'id')->latest();
    }

    /**
     * Get or initialize commission record for this tenant.
     */
    public function getOrCreateCommission(): ShopCommission
    {
        return $this->commission ?: ShopCommission::create([
            'tenant_id' => $this->id,
            'commission_rate' => 10.00,
            'max_due_limit' => 1000.00,
            'min_due_limit' => 200.00,
            'billing_day' => 1,
            'marketplace_status' => 'active',
        ]);
    }

    /**
     * Check if this shop is active and allowed on the central Mega Marketplace.
     */
    public function isMarketplaceActive(): bool
    {
        if ($this->isSuspended()) {
            return false;
        }

        $comm = $this->commission;
        return !$comm || $comm->isMarketplaceActive();
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

