<?php

namespace App\Models\Tenant;

use App\Models\Item;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';

    protected $fillable = [
        'global_item_id',
        'barcode',
        'name',
        'retail_price',
        'wholesale_price',
        'cost_price',
        'stock_quantity',
        'alert_quantity',
        'unit',
        'is_custom',
        'photo',
        'status',
    ];

    protected $casts = [
        'global_item_id' => 'integer',
        'retail_price' => 'decimal:2',
        'wholesale_price' => 'decimal:2',
        'cost_price' => 'decimal:2',
        'stock_quantity' => 'decimal:2',
        'alert_quantity' => 'decimal:2',
        'is_custom' => 'boolean',
        'status' => 'boolean',
    ];

    /**
     * Get the corresponding global/central item (if not custom).
     */
    public function globalItem()
    {
        return $this->belongsTo(Item::class, 'global_item_id');
    }

    /**
     * Scope for custom local products.
     */
    public function scopeCustom($query)
    {
        return $query->where('is_custom', true);
    }

    /**
     * Scope for imported global products.
     */
    public function scopeImported($query)
    {
        return $query->where('is_custom', false);
    }

    /**
     * Scope for low stock products.
     */
    public function scopeLowStock($query)
    {
        return $query->whereColumn('stock_quantity', '<=', 'alert_quantity');
    }
}

