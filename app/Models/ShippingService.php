<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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
        'default_base_shipping_charge',
        'default_per_kg_extra_charge',
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

            if (! $item->shipping_weight || $item->shipping_weight <= 0) {
                continue;
            }

            $itemWeight = (float) $item->shipping_weight;
            $totalWeight += $itemWeight * (int) ($cartItem['qty'] ?? 1);
        }

        return $totalWeight;
    }

    public function calculatedPrice($district, $cart): float
    {
        if (!(bool) $this->is_automated) {
            return (float) $this->price;
        }

        $districtRate = District::query()
            ->whereRaw('LOWER(name) = ?', [Str::lower(trim((string) $district))])
            ->first();

        $basePrice = $districtRate && $districtRate->base_shipping_charge !== null
            ? (float) $districtRate->base_shipping_charge
            : (float) ($this->default_base_shipping_charge ?? $this->outside_dhaka_price ?? $this->price);

        $perKgPrice = $districtRate && $districtRate->per_kg_extra_charge !== null
            ? (float) $districtRate->per_kg_extra_charge
            : (float) ($this->default_per_kg_extra_charge ?? $this->per_kg_price ?? 0);

        $weight = static::cartWeight($cart);

        if ($weight <= 0 || $weight <= 1) {
            return $basePrice;
        }

        return $basePrice + max(0, $weight - 1) * $perKgPrice;
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
