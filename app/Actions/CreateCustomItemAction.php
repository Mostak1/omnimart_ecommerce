<?php

namespace App\Actions;

use App\Models\Tenant\Product;
use InvalidArgumentException;

class CreateCustomItemAction
{
    /**
     * Create a store-specific custom local product.
     *
     * @param array $data
     * @return Product
     *
     * @throws InvalidArgumentException
     */
    public function execute(array $data): Product
    {
        if (empty($data['name']) || !is_string($data['name'])) {
            throw new InvalidArgumentException("Product name is required.");
        }

        $retailPrice = isset($data['retail_price']) ? (float)$data['retail_price'] : 0.00;
        if ($retailPrice < 0) {
            throw new InvalidArgumentException("Retail price cannot be negative.");
        }

        $costPrice = isset($data['cost_price']) ? (float)$data['cost_price'] : 0.00;
        if ($costPrice < 0) {
            throw new InvalidArgumentException("Cost price cannot be negative.");
        }

        $wholesalePrice = isset($data['wholesale_price']) ? (float)$data['wholesale_price'] : 0.00;
        $stockQuantity = isset($data['stock_quantity']) ? (float)$data['stock_quantity'] : 0.00;
        $alertQuantity = isset($data['alert_quantity']) ? (float)$data['alert_quantity'] : 5.00;
        $unit = !empty($data['unit']) ? trim($data['unit']) : 'pcs';
        $barcode = !empty($data['barcode']) ? trim($data['barcode']) : null;
        $photo = !empty($data['photo']) ? $data['photo'] : null;
        $status = array_key_exists('status', $data) ? (bool)$data['status'] : true;

        return Product::create([
            'global_item_id' => null,
            'barcode' => $barcode,
            'name' => trim($data['name']),
            'retail_price' => $retailPrice,
            'wholesale_price' => $wholesalePrice,
            'cost_price' => $costPrice,
            'stock_quantity' => $stockQuantity,
            'alert_quantity' => $alertQuantity,
            'unit' => $unit,
            'is_custom' => true,
            'photo' => $photo,
            'status' => $status,
        ]);
    }
}

