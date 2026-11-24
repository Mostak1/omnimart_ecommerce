<?php

declare(strict_types=1);

namespace App\Actions;

use App\Models\ShopCommission;
use App\Models\ShopCommissionLog;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessMarketplaceCommissionAction
{
    /**
     * Record a commission charge when a shop's product sells on the Central Mega Marketplace.
     */
    public function recordOrderCommission(string $tenantId, ?int $orderId, float $orderAmount, ?string $notes = null): ShopCommissionLog
    {
        return DB::transaction(function () use ($tenantId, $orderId, $orderAmount, $notes) {
            $tenant = Tenant::findOrFail($tenantId);
            $commission = $tenant->getOrCreateCommission();

            $commissionRate = (float)$commission->commission_rate;
            $commissionAmount = round($orderAmount * ($commissionRate / 100), 2);

            $balanceBefore = (float)$commission->current_due;
            $balanceAfter = round($balanceBefore + $commissionAmount, 2);

            $commission->current_due = $balanceAfter;
            $commission->total_earned_commission = round((float)$commission->total_earned_commission + $commissionAmount, 2);

            // Auto-pause shop on Mega Marketplace if due exceeds max credit limit
            if ($commission->current_due >= $commission->max_due_limit) {
                $commission->marketplace_status = 'paused_due_limit';
                Log::warning("Shop [{$tenantId}] has exceeded Max Due Limit (Due: {$commission->current_due}, Limit: {$commission->max_due_limit}). Auto-paused on Mega Marketplace.");
            }

            $commission->save();

            $log = ShopCommissionLog::create([
                'tenant_id' => $tenantId,
                'order_id' => $orderId,
                'type' => 'order_commission',
                'amount' => $commissionAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'status' => 'completed',
                'notes' => $notes ?: "Marketplace commission ({$commissionRate}%) on Order #{$orderId} (Amount: ৳{$orderAmount})",
            ]);

            return $log;
        });
    }

    /**
     * Record a payment made by a shop to clear their commission due (e.g. bKash/Nagad/Bank).
     */
    public function recordCommissionPayment(
        string $tenantId,
        float $paidAmount,
        string $method = 'bkash',
        ?string $trxId = null,
        ?string $notes = null
    ): ShopCommissionLog {
        return DB::transaction(function () use ($tenantId, $paidAmount, $method, $trxId, $notes) {
            $tenant = Tenant::findOrFail($tenantId);
            $commission = $tenant->getOrCreateCommission();

            $balanceBefore = (float)$commission->current_due;
            $balanceAfter = max(0.0, round($balanceBefore - $paidAmount, 2));

            $commission->current_due = $balanceAfter;
            $commission->total_paid_commission = round((float)$commission->total_paid_commission + $paidAmount, 2);
            $commission->last_paid_at = now();

            // Auto-resume shop on Mega Marketplace if due is now below max limit
            if ($commission->marketplace_status === 'paused_due_limit' && $commission->current_due < $commission->max_due_limit) {
                $commission->marketplace_status = 'active';
                Log::info("Shop [{$tenantId}] cleared due to ৳{$commission->current_due} (below limit ৳{$commission->max_due_limit}). Auto-resumed on Mega Marketplace.");
            }

            $commission->save();

            $log = ShopCommissionLog::create([
                'tenant_id' => $tenantId,
                'type' => 'commission_payment',
                'amount' => $paidAmount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => strtolower($method),
                'trx_id' => $trxId,
                'status' => 'completed',
                'notes' => $notes ?: "Due commission paid via " . strtoupper($method) . ($trxId ? " (TrxID: {$trxId})" : ""),
            ]);

            return $log;
        });
    }

    /**
     * Manual adjustment by Super Admin.
     */
    public function recordAdjustment(string $tenantId, float $amount, string $direction = 'credit', ?string $notes = null): ShopCommissionLog
    {
        return DB::transaction(function () use ($tenantId, $amount, $direction, $notes) {
            $tenant = Tenant::findOrFail($tenantId);
            $commission = $tenant->getOrCreateCommission();

            $balanceBefore = (float)$commission->current_due;
            if ($direction === 'debit') {
                // Add to due
                $balanceAfter = round($balanceBefore + $amount, 2);
            } else {
                // Deduct from due (credit/waiver)
                $balanceAfter = max(0.0, round($balanceBefore - $amount, 2));
            }

            $commission->current_due = $balanceAfter;

            if ($commission->current_due < $commission->max_due_limit && $commission->marketplace_status === 'paused_due_limit') {
                $commission->marketplace_status = 'active';
            } elseif ($commission->current_due >= $commission->max_due_limit) {
                $commission->marketplace_status = 'paused_due_limit';
            }

            $commission->save();

            return ShopCommissionLog::create([
                'tenant_id' => $tenantId,
                'type' => 'adjustment',
                'amount' => $amount,
                'balance_before' => $balanceBefore,
                'balance_after' => $balanceAfter,
                'payment_method' => 'manual_admin',
                'status' => 'completed',
                'notes' => $notes ?: "Admin manual adjustment ({$direction}) of ৳{$amount}",
            ]);
        });
    }
}
