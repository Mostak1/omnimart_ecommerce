<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TenantProductController;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyBySubdomain;
use Stancl\Tenancy\Middleware\PreventAccessFromCentralDomains;

/*
|--------------------------------------------------------------------------
| Tenant Routes
|--------------------------------------------------------------------------
|
| Here you can register the tenant routes for your application.
| These routes are loaded by the TenancyServiceProvider.
|
*/

// Tenant Web Routes
Route::middleware([
    'web',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->group(function () {
    Route::get('/tenant-test', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Tenant subdomain route works perfectly!',
            'tenant_id' => tenant('id'),
            'shop_name' => tenant('shop_name'),
            'owner_name' => tenant('owner_name'),
            'phone' => tenant('phone'),
            'address' => tenant('address'),
            'shop_type' => tenant('shop_type'),
            'status' => tenant('status'),
            'database' => tenant()->database()->getName(),
        ]);
    })->name('tenant.test');
});

// Tenant API Routes
Route::middleware([
    'api',
    InitializeTenancyBySubdomain::class,
    PreventAccessFromCentralDomains::class,
])->prefix('api/tenant')->name('tenant.api.')->group(function () {
    // Central catalog search & inspection
    Route::get('/catalog/search', [TenantProductController::class, 'searchCentralCatalog'])->name('catalog.search');
    Route::get('/catalog/items/{id}', [TenantProductController::class, 'showCentralItem'])->name('catalog.item');

    // Stock import actions
    Route::post('/catalog/import', [TenantProductController::class, 'importGlobalItem'])->name('catalog.import');
    Route::post('/catalog/bulk-import', [TenantProductController::class, 'bulkImport'])->name('catalog.bulk-import');

    // Local custom product actions
    Route::post('/products/custom', [TenantProductController::class, 'createCustomProduct'])->name('products.custom');

    // Tenant inventory listing
    Route::get('/products', [TenantProductController::class, 'index'])->name('products.index');
});
