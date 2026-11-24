<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    /**
     * Show store and invoice settings view.
     */
    public function index(): View
    {
        $settings = TenantSetting::getAll();

        return view('tenant.settings.index', compact('settings'));
    }

    /**
     * Update store and invoice settings.
     */
    public function update(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_name' => 'required|string|max:150',
            'phone' => 'nullable|string|max:25',
            'email' => 'nullable|email|max:100',
            'address' => 'nullable|string|max:300',
            'shop_slogan' => 'nullable|string|max:200',
            'vat_percentage' => 'nullable|numeric|min:0|max:100',
            'currency_symbol' => 'nullable|string|max:10',
            'invoice_footer_note' => 'nullable|string|max:500',
        ]);

        $keys = [
            'shop_name',
            'shop_slogan',
            'phone',
            'email',
            'address',
            'vat_percentage',
            'currency_symbol',
            'invoice_footer_note',
            'pos_auto_print',
        ];

        foreach ($keys as $key) {
            if ($request->has($key)) {
                TenantSetting::set($key, $request->input($key));
            }
        }

        // Handle auto print checkbox (if unchecked, it's not present in request)
        TenantSetting::set('pos_auto_print', $request->has('pos_auto_print') ? '1' : '0');

        // Sync with central tenant model if available
        if (function_exists('tenant') && tenant()) {
            tenant()->update([
                'shop_name' => $request->input('shop_name'),
                'phone' => $request->input('phone'),
                'address' => $request->input('address'),
            ]);
        }

        return back()->with('success', 'দোকানের সেটিংস ও ইনভয়েস কনফিগারেশন সফলভাবে আপডেট হয়েছে!');
    }
}
