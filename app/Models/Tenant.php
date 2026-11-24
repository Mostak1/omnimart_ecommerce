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

