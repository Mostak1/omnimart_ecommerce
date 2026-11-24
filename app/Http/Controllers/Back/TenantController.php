<?php

namespace App\Http\Controllers\Back;

use App\Http\Controllers\Controller;
use App\Models\District;
use App\Models\PoliceStation;
use App\Models\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class TenantController extends Controller
{
    /**
     * Constructor Method.
     */
    public function __construct()
    {
        $this->middleware('auth:admin');
        $this->middleware('adminlocalize');
    }

    /**
     * Display a listing of all tenants/shops.
     */
    public function index(): View
    {
        $tenants = Tenant::with('domains')->latest()->get();
        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';

        $totalShops = $tenants->count();
        $activeShops = $tenants->where('status', 'active')->count();
        $suspendedShops = $tenants->where('status', 'suspended')->count();
        $expiringSoon = $tenants->filter(fn($t) => $t->daysRemaining() <= 7)->count();

        return view('back.tenants.index', compact(
            'tenants',
            'centralDomain',
            'totalShops',
            'activeShops',
            'suspendedShops',
            'expiringSoon'
        ));
    }

    /**
     * Show the form for creating a new tenant/shop.
     */
    public function create(): View
    {
        $districts = District::with('policeStations')->ordered()->get();
        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';

        return view('back.tenants.create', compact('districts', 'centralDomain'));
    }

    /**
     * Store a newly created tenant/shop and provision its database.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'shop_name' => 'required|string|max:255',
            'subdomain' => [
                'required',
                'string',
                'alpha_dash',
                'min:3',
                'max:50',
                'unique:tenants,id',
                'unique:domains,domain',
                'not_in:admin,www,api,mail,central,localhost,root,system',
            ],
            'owner_name' => 'required|string|max:255',
            'phone' => 'required|string|max:30',
            'district_id' => 'nullable|exists:districts,id',
            'police_station_id' => 'nullable|exists:police_stations,id',
            'address' => 'nullable|string|max:500',
            'password' => 'required|string|min:6',
            'shop_type' => 'nullable|string|max:100',
            'db_mode' => 'nullable|in:auto,cpanel',
            'custom_db_name' => 'nullable|string|max:100',
            'custom_db_user' => 'nullable|string|max:100',
            'custom_db_pass' => 'nullable|string|max:100',
        ]);

        if ($request->input('db_mode') === 'cpanel') {
            $request->validate([
                'custom_db_name' => 'required|string|max:100',
            ], [
                'custom_db_name.required' => 'cPanel মোড সিলেক্ট করলে cPanel-এ তৈরি করা Database Name দেওয়া আবশ্যক।',
            ]);
        }

        $subdomain = strtolower(trim($request->subdomain));

        // Format detailed address including District and Police Station if provided
        $district = $request->district_id ? District::find($request->district_id) : null;
        $policeStation = $request->police_station_id ? PoliceStation::find($request->police_station_id) : null;

        $addressParts = array_filter([
            $request->address,
            $policeStation ? "PS: {$policeStation->name}" : null,
            $district ? "Dist: {$district->name}" : null,
        ]);
        $fullAddress = implode(', ', $addressParts);

        $tenantData = [
            'district_id' => $request->district_id,
            'district_name' => $district?->name,
            'police_station_id' => $request->police_station_id,
            'police_station_name' => $policeStation?->name,
            'admin_password_hash' => bcrypt($request->password),
            'plan_name' => 'Starter (ট্রায়াল)',
            'plan_price' => 0,
            'expires_at' => now()->addDays(30)->toDateString(),
            'db_setup_mode' => $request->input('db_mode', 'auto'),
        ];

        // Configure custom cPanel database credentials if specified
        if ($request->input('db_mode') === 'cpanel' && $request->filled('custom_db_name')) {
            $customDbName = trim($request->input('custom_db_name'));
            $tenantData['tenancy_db_name'] = $customDbName;
            $tenantData['create_database'] = false; // Existing cPanel database

            if ($request->filled('custom_db_user')) {
                $tenantData['tenancy_db_username'] = trim($request->input('custom_db_user'));
            }
            if ($request->filled('custom_db_pass')) {
                $tenantData['tenancy_db_password'] = $request->input('custom_db_pass');
            }
        }

        // 1. Create Tenant (fires Events\TenantCreated -> creates/connects MySQL db & runs migrations)
        $tenantAttributes = array_merge([
            'id' => $subdomain,
            'shop_name' => $request->shop_name,
            'owner_name' => $request->owner_name,
            'phone' => $request->phone,
            'address' => $fullAddress ?: $request->address,
            'shop_type' => $request->shop_type ?: 'general',
            'status' => 'active',
        ], $tenantData);

        $tenant = Tenant::create($tenantAttributes);

        // 2. Create Domain mapping
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        // 3. Seed initial store owner in tenant database
        try {
            $tenant->run(function () use ($tenant, $request) {
                \App\Models\Tenant\TenantUser::firstOrCreate(
                    ['phone' => $tenant->phone],
                    [
                        'name' => $tenant->owner_name,
                        'email' => $request->email ?: ($tenant->id . '@store.local'),
                        'password' => bcrypt($request->password),
                        'role' => 'owner',
                        'is_active' => true,
                    ]
                );
            });
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning("Could not seed tenant owner: " . $e->getMessage());
        }

        $dbName = $tenant->database()->getName();
        $modeLabel = $request->input('db_mode') === 'cpanel' ? 'cPanel Database' : 'Auto Created';

        return redirect()->route('back.tenants.index')
            ->withSuccess(__("Shop [:shop] with Subdomain [:domain] and Database [:db] (:mode) created successfully!", [
                'shop' => $tenant->shop_name,
                'domain' => "{$subdomain}." . (config('tenancy.central_domains')[0] ?? 'localhost'),
                'db' => $dbName,
                'mode' => $modeLabel,
            ]));
    }

    /**
     * Toggle or update tenant status (active / suspended).
     */
    public function status(string $id, string $status): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $newStatus = in_array(strtolower($status), ['active', '1']) ? 'active' : 'suspended';

        $tenant->update(['status' => $newStatus]);

        return redirect()->route('back.tenants.index')
            ->withSuccess(__('Shop [:shop] status updated to [:status]!', [
                'shop' => $tenant->shop_name,
                'status' => ucfirst($newStatus),
            ]));
    }

    /**
     * Update tenant SaaS subscription plan and expiration date.
     */
    public function updateSubscription(Request $request, string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);

        $request->validate([
            'plan_name' => 'required|string|max:100',
            'expires_at' => 'nullable|date',
            'extend_days' => 'nullable|integer',
        ]);

        $data = $tenant->data ?? [];
        if (is_string($data)) {
            $data = json_decode($data, true) ?: [];
        }
        $data['plan_name'] = $request->input('plan_name');
        $data['plan_price'] = $request->input('plan_price', 0);

        $tenant->plan_name = $request->input('plan_name', 'Starter (ট্রায়াল)');

        if ($request->filled('extend_days')) {
            $baseDate = $tenant->expires_at && $tenant->expires_at->isFuture() ? $tenant->expires_at : now();
            $tenant->expires_at = $baseDate->copy()->addDays((int)$request->input('extend_days'))->toDateString();
        } elseif ($request->filled('expires_at')) {
            $tenant->expires_at = $request->input('expires_at');
        }

        $tenant->status = 'active';
        $tenant->save();

        return redirect()->route('back.tenants.index')
            ->withSuccess(__("Shop [:shop] subscription updated to [:plan] (Expires: :exp)!", [
                'shop' => $tenant->shop_name,
                'plan' => $tenant->plan_name,
                'exp' => $tenant->expires_at ? $tenant->expires_at->format('d M Y') : 'Never',
            ]));
    }

    /**
     * Remove the specified tenant/shop and drop its isolated database.
     */
    public function destroy(string $id): RedirectResponse
    {
        $tenant = Tenant::findOrFail($id);
        $shopName = $tenant->shop_name;

        // Deleting the tenant triggers Events\TenantDeleted which drops the tenant database
        $tenant->delete();

        return redirect()->route('back.tenants.index')
            ->withSuccess(__('Shop [:shop] and its isolated database were deleted successfully.', [
                'shop' => $shopName,
            ]));
    }

    /**
     * AJAX endpoint to fetch police stations by district.
     */
    public function getPoliceStations(int $district_id)
    {
        $policeStations = PoliceStation::where('district_id', $district_id)
            ->where('status', 1)
            ->orderBy('name')
            ->get();

        return response()->json($policeStations);
    }
}

