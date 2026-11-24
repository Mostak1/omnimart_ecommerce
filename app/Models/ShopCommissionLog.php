<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\CentralConnection;

class ShopCommissionLog extends Model
{
    use CentralConnection;

    protected $table = 'shop_commission_logs';

    protected $fillable = [
        'tenant_id',
        'order_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'payment_method',
        'trx_id',
        'status',
        'notes',
    ];

    protected $casts = [
        'amount' => 'float',
        'balance_before' => 'float',
        'balance_after' => 'float',
    ];

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id', 'id');
    }
}
