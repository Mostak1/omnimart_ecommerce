<?php

declare(strict_types=1);

namespace App\Http\Controllers\Back;

use App\Actions\ProcessMarketplaceCommissionAction;
use App\Http\Controllers\Controller;
use App\Models\ShopCommission;
use App\Models\ShopCommissionLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class MarketplaceCommissionController extends Controller
{
    /**
     * Display the Marketplace Commission Overview & Shop Balances.
     */
    public function index()
    {
        // Ensure all active tenants have a commission record
        foreach (Tenant::all() as $t) {
            $t->getOrCreateCommission();
        }

        $commissions = ShopCommission::with('tenant')->get();

        $metrics = [
            'total_earned' => (float)ShopCommission::sum('total_earned_commission'),
            'total_due' => (float)ShopCommission::sum('current_due'),
            'total_paid' => (float)ShopCommission::sum('total_paid_commission'),
            'paused_count' => ShopCommission::where('marketplace_status', 'paused_due_limit')->count(),
            'total_shops' => $commissions->count(),
        ];

        return view('back.commissions.index', compact('commissions', 'metrics'));
    }

    /**
     * Update a shop's commission rate and dual due limits.
     */
    public function updateLimits(Request $request, int $id): RedirectResponse
    {
        $request->validate([
            'commission_rate' => 'required|numeric|min:0|max:100',
            'max_due_limit' => 'required|numeric|min:0',
            'min_due_limit' => 'required|numeric|min:0',
            'billing_day' => 'required|integer|min:1|max:28',
            'marketplace_status' => 'required|in:active,paused_due_limit,suspended',
        ]);

        $comm = ShopCommission::findOrFail($id);
        $comm->update([
            'commission_rate' => (float)$request->commission_rate,
            'max_due_limit' => (float)$request->max_due_limit,
            'min_due_limit' => (float)$request->min_due_limit,
            'billing_day' => (int)$request->billing_day,
            'marketplace_status' => $request->marketplace_status,
        ]);

        return redirect()->route('back.commissions.index')
            ->withSuccess(__("Shop [:shop] commission settings updated successfully!", [
                'shop' => $comm->tenant?->shop_name ?? $comm->tenant_id,
            ]));
    }

    /**
     * Record a manual payment or adjustment for a shop.
     */
    public function recordPayment(Request $request, int $id, ProcessMarketplaceCommissionAction $action): RedirectResponse
    {
        $request->validate([
            'type' => 'required|in:payment,adjustment_credit,adjustment_debit',
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'nullable|string|max:50',
            'trx_id' => 'nullable|string|max:100',
            'notes' => 'nullable|string|max:500',
        ]);

        $comm = ShopCommission::findOrFail($id);
        $amount = (float)$request->amount;

        if ($request->type === 'payment') {
            $action->recordCommissionPayment(
                $comm->tenant_id,
                $amount,
                $request->payment_method ?: 'manual_admin',
                $request->trx_id,
                $request->notes ?: "Payment recorded manually by Super Admin"
            );
            $msg = "Recorded payment of ৳{$amount} for shop [{$comm->tenant?->shop_name}]";
        } elseif ($request->type === 'adjustment_credit') {
            $action->recordAdjustment($comm->tenant_id, $amount, 'credit', $request->notes);
            $msg = "Credited/waived ৳{$amount} from due of shop [{$comm->tenant?->shop_name}]";
        } else {
            $action->recordAdjustment($comm->tenant_id, $amount, 'debit', $request->notes);
            $msg = "Added penalty/debit of ৳{$amount} to due of shop [{$comm->tenant?->shop_name}]";
        }

        return redirect()->route('back.commissions.index')->withSuccess($msg);
    }

    /**
     * Complete Transaction & Ledger Audit History.
     */
    public function logs(Request $request)
    {
        $query = ShopCommissionLog::with('tenant')->latest('id');

        if ($request->filled('tenant_id') && $request->tenant_id !== 'all') {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(25)->withQueryString();
        $tenants = Tenant::all();

        return view('back.commissions.logs', compact('logs', 'tenants'));
    }
}
