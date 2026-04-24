<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class District extends Model
{
    protected $fillable = [
        'name',
        'base_shipping_charge',
        'per_kg_extra_charge',
    ];

    public function scopeOrdered($query)
    {
        return $query->orderBy('name');
    }
}
