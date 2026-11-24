<?php

namespace App\Actions;

use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\SaleItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessPosSaleAction
{
    /**
     * Process a complete POS sale: creates sale, items, and decrements product inventory.
     *
     * @param array $payload
     * @param int|null $userId
     * @return Sale
     */
    public function execute(array $payload, ?int $userId = null): Sale
    {
        $items = $payload['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException("কার্টে কোনো পণ্য নেই। অন্তত একটি পণ্য নির্বাচন করুন।");
        }

        return DB::transaction(function () use ($payload, $items, $userId) {
            // 1. Generate unique invoice number: INV-YYYYMMDD-XXXX
            $today = date('Ymd');
            $countToday = Sale::whereDate('created_at', today())->count() + 1;
            $invoiceNo = 'INV-' . $today . '-' . str_pad((string)$countToday, 4, '0', STR_PAD_LEFT);

            $subtotalSum = 0;
            $preparedItems = [];

            // 2. Validate and prepare items
            foreach ($items as $itemData) {
                $productId = $itemData['product_id'] ?? null;
                $quantity = max(0.01, (float)($itemData['quantity'] ?? 1));
                $unitPrice = (float)($itemData['unit_price'] ?? 0);
                $discount = (float)($itemData['discount'] ?? 0);

                $product = null;
                if ($productId) {
                    $product = Product::lockForUpdate()->find($productId);
                }

                $productName = $itemData['name'] ?? ($product ? $product->name : 'পণ্য');
                if ($product && !isset($itemData['unit_price'])) {
                    $unitPrice = (float)$product->retail_price;
                }

                $itemSubtotal = $unitPrice * $quantity;
                $itemTotal = max(0, $itemSubtotal - $discount);
                $subtotalSum += $itemSubtotal;

                $preparedItems[] = [
                    'product_id' => $product ? $product->id : null,
                    'product' => $product,
                    'product_name' => $productName,
                    'unit_price' => $unitPrice,
                    'quantity' => $quantity,
                    'subtotal' => $itemSubtotal,
                    'discount' => $discount,
                    'total' => $itemTotal,
                ];
            }

            // 3. Overall financial calculations
            $overallDiscount = (float)($payload['discount_amount'] ?? 0);
            $taxAmount = (float)($payload['tax_amount'] ?? 0);
            $netTotal = max(0, ($subtotalSum - $overallDiscount) + $taxAmount);

            $paidAmount = isset($payload['paid_amount']) ? (float)$payload['paid_amount'] : $netTotal;
            $dueAmount = max(0, $netTotal - $paidAmount);

            $paymentMethod = $payload['payment_method'] ?? 'cash';
            $paymentStatus = 'paid';
            if ($dueAmount > 0 && $paidAmount > 0) {
                $paymentStatus = 'partial';
            } elseif ($dueAmount > 0 && $paidAmount <= 0) {
                $paymentStatus = 'due';
            }

            // 4. Create Sale
            $sale = Sale::create([
                'invoice_no' => $invoiceNo,
                'customer_name' => !empty($payload['customer_name']) ? trim($payload['customer_name']) : 'সাধারণ কাস্টমার',
                'customer_phone' => !empty($payload['customer_phone']) ? trim($payload['customer_phone']) : null,
                'total_amount' => $netTotal,
                'discount_amount' => $overallDiscount,
                'tax_amount' => $taxAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'created_by' => $userId,
                'notes' => $payload['notes'] ?? null,
            ]);

            // 5. Create SaleItems & Decrement stock
            foreach ($preparedItems as $prep) {
                SaleItem::create([
                    'sale_id' => $sale->id,
                    'product_id' => $prep['product_id'],
                    'product_name' => $prep['product_name'],
                    'unit_price' => $prep['unit_price'],
                    'quantity' => $prep['quantity'],
                    'subtotal' => $prep['subtotal'],
                    'discount' => $prep['discount'],
                    'total' => $prep['total'],
                ]);

                // Stock reduction
                if ($prep['product']) {
                    $prep['product']->decrement('stock_quantity', $prep['quantity']);
                }
            }

            return $sale->load(['items', 'creator']);
        });
    }
}
