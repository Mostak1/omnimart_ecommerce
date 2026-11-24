<?php

declare(strict_types=1);

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\Item;
use App\Models\Tenant;
use Illuminate\Http\Request;

class MarketplaceShopController extends Controller
{
    /**
     * Browse all registered shops on the Central Mega Marketplace.
     */
    public function index(Request $request)
    {
        $query = Tenant::where('status', 'active')->with(['commission', 'domains']);

        if ($request->filled('search')) {
            $s = trim($request->search);
            $query->where(function ($q) use ($s) {
                $q->where('shop_name', 'like', "%{$s}%")
                  ->orWhere('owner_name', 'like', "%{$s}%")
                  ->orWhere('address', 'like', "%{$s}%");
            });
        }

        if ($request->filled('type') && $request->type !== 'all') {
            $query->where('shop_type', $request->type);
        }

        $shops = $query->paginate(12)->withQueryString();

        $shopTypes = Tenant::where('status', 'active')
            ->whereNotNull('shop_type')
            ->distinct()
            ->pluck('shop_type');

        return view('front.marketplace.shops', compact('shops', 'shopTypes'));
    }

    /**
     * View specific shop details and products on the Mega Marketplace.
     */
    public function show(string $subdomain)
    {
        $shop = Tenant::with(['commission', 'domains'])->findOrFail($subdomain);

        // Fetch central products tagged to this shop
        $items = Item::where('tenant_id', $shop->id)
            ->where('status', 1)
            ->latest('id')
            ->paginate(16);

        return view('front.marketplace.shop_profile', compact('shop', 'items'));
    }
}
