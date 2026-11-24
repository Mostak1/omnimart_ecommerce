<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class ShopCommission extends Model
{
    use CentralConnection;

    protected $table = 'shop_commissions';

    protected $fillable = [
        'tenant_id',
        'commission_rate',
        'current_due',
        'total_earned_commission',
        'total_paid_commission',
        'max_due_limit',
        'min_due_limit',
        'billing_day',
        'marketplace_status',
        'last_billed_at',
        'last_paid_at',
    ];

    protected $casts = [
        'commission_rate' => 'float',
        'current_due' => 'float',
        'total_earned_commission' => 'float',
        'total_paid_commission' => 'float',
        'max_due_limit' => 'float',
        'min_due_limit' => 'float',
        'billing_day' => 'integer',
        'last_billed_at' => 'date',
        'last_paid_at' => 'datetime',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(ShopCommissionLog::class, 'tenant_id', 'tenant_id')->latest();
    }

    /**
     * Check if shop due exceeds maximum credit limit.
     */
    public function isOverdueLimit(): bool
    {
        return $this->current_due >= $this->max_due_limit;
    }

    /**
     * Check if shop is currently active on the Mega Marketplace.
     */
    public function isMarketplaceActive(): bool
    {
        return $this->marketplace_status === 'active';
    }

    /**
     * Available credit remaining before reaching max due limit.
     */
    public function availableCredit(): float
    {
        return max(0.0, (float)($this->max_due_limit - $this->current_due));
    }
}
