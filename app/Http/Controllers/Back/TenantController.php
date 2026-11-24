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

        return view('back.tenants.index', compact('tenants', 'centralDomain'));
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
        ]);

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

        // 1. Create Tenant (fires Events\TenantCreated -> creates MySQL db & runs tenant migrations)
        $tenant = Tenant::create([
            'id' => $subdomain,
            'shop_name' => $request->shop_name,
            'owner_name' => $request->owner_name,
            'phone' => $request->phone,
            'address' => $fullAddress ?: $request->address,
            'shop_type' => $request->shop_type ?: 'general',
            'status' => 'active',
            'data' => [
                'district_id' => $request->district_id,
                'district_name' => $district?->name,
                'police_station_id' => $request->police_station_id,
                'police_station_name' => $policeStation?->name,
                'admin_password_hash' => bcrypt($request->password),
            ],
        ]);

        // 2. Create Domain mapping
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        $dbName = $tenant->database()->getName();

        return redirect()->route('back.tenants.index')
            ->withSuccess(__("Shop [:shop] with Subdomain [:domain] and Database [:db] created successfully!", [
                'shop' => $tenant->shop_name,
                'domain' => "{$subdomain}." . (config('tenancy.central_domains')[0] ?? 'localhost'),
                'db' => $dbName,
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

