<?php

namespace App\Actions;

use App\Models\Item;
use App\Models\Tenant\Product;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use InvalidArgumentException;

class ImportGlobalItemAction
{
    /**
     * Import or update a central catalog item in the tenant's local product stock.
     *
     * @param Item|int $item
     * @param array $attributes Custom retail_price, cost_price, stock_quantity, etc.
     * @return Product
     *
     * @throws ModelNotFoundException
     * @throws InvalidArgumentException
     */
    public function execute(Item|int $item, array $attributes = []): Product
    {
        // 1. Resolve central Item model
        if (is_numeric($item)) {
            $item = Item::with('category')->find($item);
            if (!$item) {
                throw new ModelNotFoundException("Central Item with ID [{$item}] was not found.");
            }
        } elseif (!$item instanceof Item) {
            throw new InvalidArgumentException("Item must be an instance of App\Models\Item or a valid integer ID.");
        }

        // 2. Extract or fallback properties from central item
        $name = !empty($attributes['name']) ? trim($attributes['name']) : $item->name;
        $barcode = !empty($attributes['barcode']) ? trim($attributes['barcode']) : ($item->sku ?? null);
        $photo = !empty($attributes['photo']) ? $attributes['photo'] : ($item->photo ?? $item->thumbnail ?? null);

        // Retail price default from central item if not supplied
        $defaultRetailPrice = (float)($item->discount_price > 0 ? $item->discount_price : ($item->previous_price ?? 0.00));
        $retailPrice = isset($attributes['retail_price']) && is_numeric($attributes['retail_price'])
            ? (float)$attributes['retail_price']
            : $defaultRetailPrice;

        $costPrice = isset($attributes['cost_price']) && is_numeric($attributes['cost_price'])
            ? (float)$attributes['cost_price']
            : 0.00;

        $wholesalePrice = isset($attributes['wholesale_price']) && is_numeric($attributes['wholesale_price'])
            ? (float)$attributes['wholesale_price']
            : 0.00;

        $stockQuantity = isset($attributes['stock_quantity']) && is_numeric($attributes['stock_quantity'])
            ? (float)$attributes['stock_quantity']
            : 0.00;

        $alertQuantity = isset($attributes['alert_quantity']) && is_numeric($attributes['alert_quantity'])
            ? (float)$attributes['alert_quantity']
            : 5.00;

        $unit = !empty($attributes['unit']) ? trim($attributes['unit']) : 'pcs';
        $status = array_key_exists('status', $attributes) ? (bool)$attributes['status'] : true;

        // 3. Find if already imported for this tenant
        $existingProduct = Product::where('global_item_id', $item->id)->first();

        if ($existingProduct) {
            $updatedStock = !empty($attributes['add_to_stock'])
                ? $existingProduct->stock_quantity + $stockQuantity
                : (array_key_exists('stock_quantity', $attributes) ? $stockQuantity : $existingProduct->stock_quantity);

            $existingProduct->update([
                'name' => $name,
                'barcode' => $barcode,
                'retail_price' => $retailPrice,
                'wholesale_price' => $wholesalePrice,
                'cost_price' => $costPrice,
                'stock_quantity' => $updatedStock,
                'alert_quantity' => $alertQuantity,
                'unit' => $unit,
                'photo' => $photo,
                'status' => $status,
                'is_custom' => false,
            ]);

            return $existingProduct->fresh();
        }

        // 4. Create new product record in tenant database
        return Product::create([
            'global_item_id' => $item->id,
            'barcode' => $barcode,
            'name' => $name,
            'retail_price' => $retailPrice,
            'wholesale_price' => $wholesalePrice,
            'cost_price' => $costPrice,
            'stock_quantity' => $stockQuantity,
            'alert_quantity' => $alertQuantity,
            'unit' => $unit,
            'is_custom' => false,
            'photo' => $photo,
            'status' => $status,
        ]);
    }
}

