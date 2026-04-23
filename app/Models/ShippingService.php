<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShippingService extends Model
{
    protected $fillable = [
        'title',
        'price',
        'status',
        'is_condition',
        'minimum_price',
        'seller_id',
        'is_automated',
        'dhaka_price',
        'outside_dhaka_price',
        'per_kg_price',
    ];
    public $timestamps = false;

    public static function activeAutomated()
    {
        return static::whereStatus(1)->where('is_automated', 1)->first()
            ?? static::whereStatus(1)->where('id', '!=', 1)->first();
    }

    public static function cartWeight($cart): float
    {
        $totalWeight = 0;

        foreach (($cart ?? []) as $key => $cartItem) {
            $itemId = (int) explode('-', $key)[0];
            $item = Item::find($itemId);

            if (!$item || $item->item_type !== 'normal') {
                continue;
            }

            $itemWeight = $item->shipping_weight && $item->shipping_weight > 0 ? (float) $item->shipping_weight : 1;
            $totalWeight += $itemWeight * (int) ($cartItem['qty'] ?? 1);
        }

        return $totalWeight > 0 ? $totalWeight : 1;
    }

    public static function isDhakaDistrict($district): bool
    {
        $district = strtolower(trim((string) $district));

        return in_array($district, ['dhaka', 'dhaka metro'], true);
    }

    public function calculatedPrice($district, $cart): float
    {
        if (!(bool) $this->is_automated) {
            return (float) $this->price;
        }

        $weight = (int) ceil(static::cartWeight($cart));
        $basePrice = static::isDhakaDistrict($district)
            ? (float) $this->dhaka_price
            : (float) $this->outside_dhaka_price;

        return $basePrice + max(0, $weight - 1) * (float) $this->per_kg_price;
    }

    public static function appliedService($district, $cart)
    {
        $service = static::activeAutomated();

        if (!$service) {
            return null;
        }

        $applied = clone $service;
        $applied->price = $service->calculatedPrice($district, $cart);
        $applied->calculated_weight = static::cartWeight($cart);
        $applied->calculated_district = $district;

        return $applied;
    }
}
