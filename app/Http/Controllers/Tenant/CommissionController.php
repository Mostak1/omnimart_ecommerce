<?php

declare(strict_types=1);

namespace App\Http\Controllers\Tenant;

use App\Actions\ProcessMarketplaceCommissionAction;
use App\Http\Controllers\Controller;
use App\Models\ShopCommission;
use App\Models\ShopCommissionLog;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class CommissionController extends Controller
{
    /**
     * Show the tenant's marketplace commission balance, due limits and transaction logs.
     */
    public function index()
    {
        $tenantId = (string)tenant('id');
        $tenant = Tenant::findOrFail($tenantId);
        $commission = $tenant->getOrCreateCommission();

        $logs = ShopCommissionLog::where('tenant_id', $tenantId)
            ->latest('id')
            ->paginate(15);

        return view('tenant.commission.index', compact('tenant', 'commission', 'logs'));
    }

    /**
     * Store owner submits payment to clear their commission due.
     */
    public function pay(Request $request, ProcessMarketplaceCommissionAction $action): RedirectResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'payment_method' => 'required|in:bkash,nagad,rocket,bank',
            'trx_id' => 'required|string|max:100',
            'notes' => 'nullable|string|max:255',
        ]);

        $tenantId = (string)tenant('id');
        $amount = (float)$request->amount;

        $action->recordCommissionPayment(
            $tenantId,
            $amount,
            $request->payment_method,
            $request->trx_id,
            $request->notes ?: "Paid via " . strtoupper($request->payment_method) . " (TrxID: {$request->trx_id})"
        );

        return redirect()->route('tenant.commission.index')
            ->withSuccess(__("৳:amount commission payment submitted successfully! Your account balance has been updated.", [
                'amount' => number_format($amount, 2),
            ]));
    }
}
