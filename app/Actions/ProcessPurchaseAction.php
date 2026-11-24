<?php

namespace App\Actions;

use App\Models\Tenant\Product;
use App\Models\Tenant\Purchase;
use App\Models\Tenant\PurchaseItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class ProcessPurchaseAction
{
    /**
     * Process a supplier purchase order: creates purchase, items, and increments product stock & updates cost price.
     *
     * @param array $payload
     * @param int|null $userId
     * @return Purchase
     */
    public function execute(array $payload, ?int $userId = null): Purchase
    {
        $items = $payload['items'] ?? [];
        if (empty($items)) {
            throw new InvalidArgumentException("চালানে কোনো পণ্য যোগ করা হয়নি। অন্তত একটি পণ্য নির্বাচন করুন।");
        }

        return DB::transaction(function () use ($payload, $items, $userId) {
            // 1. Invoice Number (use provided or auto generate)
            $invoiceNo = !empty($payload['invoice_no']) ? trim($payload['invoice_no']) : null;
            if (!$invoiceNo) {
                $today = date('Ymd');
                $count = Purchase::whereDate('created_at', today())->count() + 1;
                $invoiceNo = 'PUR-' . $today . '-' . str_pad((string)$count, 4, '0', STR_PAD_LEFT);
            }

            $totalAmount = 0;
            $preparedItems = [];

            // 2. Validate and prepare items
            foreach ($items as $entry) {
                $productId = $entry['product_id'] ?? null;
                $quantity = max(0.01, (float)($entry['quantity'] ?? 1));
                $unitCost = (float)($entry['unit_cost'] ?? 0);

                $product = null;
                if ($productId) {
                    $product = Product::lockForUpdate()->find($productId);
                }

                $productName = $entry['product_name'] ?? ($product ? $product->name : 'পণ্য');
                $lineTotal = $unitCost * $quantity;
                $totalAmount += $lineTotal;

                $preparedItems[] = [
                    'product_id' => $product ? $product->id : null,
                    'product' => $product,
                    'product_name' => $productName,
                    'unit_cost' => $unitCost,
                    'quantity' => $quantity,
                    'total_cost' => $lineTotal,
                ];
            }

            // 3. Paid & Due calculation
            $paidAmount = isset($payload['paid_amount']) ? (float)$payload['paid_amount'] : $totalAmount;
            $dueAmount = max(0, $totalAmount - $paidAmount);

            $paymentStatus = 'paid';
            if ($dueAmount > 0 && $paidAmount > 0) {
                $paymentStatus = 'partial';
            } elseif ($dueAmount > 0 && $paidAmount <= 0) {
                $paymentStatus = 'due';
            }

            // 4. Create Purchase record
            $purchase = Purchase::create([
                'invoice_no' => $invoiceNo,
                'supplier_name' => !empty($payload['supplier_name']) ? trim($payload['supplier_name']) : 'সাধারণ পাইকারি বিক্রেতা',
                'supplier_phone' => !empty($payload['supplier_phone']) ? trim($payload['supplier_phone']) : null,
                'total_amount' => $totalAmount,
                'paid_amount' => $paidAmount,
                'due_amount' => $dueAmount,
                'payment_status' => $paymentStatus,
                'purchase_date' => !empty($payload['purchase_date']) ? $payload['purchase_date'] : now()->toDateString(),
                'created_by' => $userId,
                'notes' => $payload['notes'] ?? null,
            ]);

            // 5. Create Purchase Items & Increment stock
            foreach ($preparedItems as $prep) {
                PurchaseItem::create([
                    'purchase_id' => $purchase->id,
                    'product_id' => $prep['product_id'],
                    'product_name' => $prep['product_name'],
                    'unit_cost' => $prep['unit_cost'],
                    'quantity' => $prep['quantity'],
                    'total_cost' => $prep['total_cost'],
                ]);

                // Increment stock and update cost price if product exists
                if ($prep['product']) {
                    $prep['product']->increment('stock_quantity', $prep['quantity']);
                    if ($prep['unit_cost'] > 0) {
                        $prep['product']->update(['cost_price' => $prep['unit_cost']]);
                    }
                }
            }

            return $purchase->load(['items', 'creator']);
        });
    }
}
