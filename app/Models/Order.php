<?php

namespace App\Models;
use DB;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'user_info',
        'cart',
        'shipping',
        'discount',
        'payment_method',
        'txnid',
        'charge_id',
        'transaction_number',
        'order_status',
        'payment_status',
        'shipping_info',
        'billing_info',
        'currency_sign',
        'currency_value',
        'tax',
        'state_price',
        'state',
        'steadfast_consignment_id',
        'steadfast_delivery_status',
        'steadfast_last_tracking_response',
        'steadfast_order_created_at',
    ];

    protected $casts = [
        'steadfast_order_created_at' => 'datetime',
    ];

    public function user()
    {
    	return $this->belongsTo('App\Models\User')->withDefault();
    }

    public function tracks()
    {
    	return $this->belongsTo('App\Models\TrackOrder','order_id')->withDefault();
    }

    public function tranaction()
    {
    	return $this->hasOne('App\Models\Transaction','order_id')->withDefault();
    }

    public function tracks_data()
    {
    	return $this->hasMany('App\Models\TrackOrder','order_id');
    }

    public function notificaton()
    {
    	return $this->hasMany('App\Models\Notification','order_id');
    }

    public function getBillingDataAttribute(): array
    {
        return json_decode($this->billing_info ?? '[]', true) ?: [];
    }

    public function getShippingDataAttribute(): array
    {
        return json_decode($this->shipping_info ?? '[]', true) ?: [];
    }

    public function getCustomerEmailAttribute(): ?string
    {
        $email = $this->billing_data['bill_email'] ?? null;

        return filled($email) ? $email : null;
    }

    public function getCustomerNameAttribute(): string
    {
        return (string) ($this->shipping_data['ship_first_name']
            ?? $this->billing_data['bill_first_name']
            ?? $this->user->displayName());
    }

    public function getTotalAmountAttribute(): float
    {
        return (float) \App\Helpers\PriceHelper::OrderTotal($this);
    }

}
