<?php

declare(strict_types=1);

use App\Http\Controllers\Api\TenantProductController as ApiTenantProductController;
use App\Http\Controllers\Tenant\Auth\TenantAuthController;
use App\Http\Controllers\Tenant\ProductController;
use App\Http\Controllers\Tenant\PosController;
use App\Http\Controllers\Tenant\PurchaseController;
use App\Http\Controllers\Tenant\SettingController;
use App\Http\Controllers\Tenant\StaffController;
use App\Http\Controllers\Tenant\StorefrontController;
use App\Http\Middleware\CheckTenantSubscription;
use Illuminate\Support\Facades\Route;
use Stancl\Tenancy\Middleware\InitializeTenancyByDomainOrSubdomain;
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

// =========================================================================
// 1. Tenant Web Routes (Subdomain / Custom Domain Dashboard, POS & Storefront)
// =========================================================================
Route::middleware([
    'web',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
    CheckTenantSubscription::class,
])->group(function () {
    // Diagnostic route
    Route::get('/tenant-test', function () {
        return response()->json([
            'status' => 'success',
            'message' => 'Tenant subdomain route works perfectly!',
            'tenant_id' => tenant('id'),
            'shop_name' => tenant('shop_name'),
            'database' => tenant()->database()->getName(),
        ]);
    })->name('tenant.test');

    // Public eCommerce Storefront Routes
    Route::get('/', [StorefrontController::class, 'index'])->name('tenant.storefront.index');
    Route::post('/order/checkout', [StorefrontController::class, 'placeOrder'])->name('tenant.storefront.checkout');
    Route::get('/order/success/{invoiceNo}', [StorefrontController::class, 'orderSuccess'])->name('tenant.storefront.order-success');

    // Tenant Authentication Routes
    Route::get('/login', [TenantAuthController::class, 'showLoginForm'])->name('tenant.login');
    Route::post('/login', [TenantAuthController::class, 'login'])->name('tenant.login.submit');
    Route::post('/logout', [TenantAuthController::class, 'logout'])->name('tenant.logout');

    // Route Aliases for easy access (/admin or /tenant-admin)
    Route::get('/admin/login', [TenantAuthController::class, 'showLoginForm']);
    Route::get('/tenant-admin/login', [TenantAuthController::class, 'showLoginForm']);

    Route::get('/admin', function () {
        return redirect()->route('tenant.products.index');
    });

    Route::get('/tenant-admin', function () {
        return redirect()->route('tenant.products.index');
    });

    // Protected Store Owner Product & Inventory Management
    Route::middleware(['auth:tenant_web'])->prefix('tenant-admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index'])->name('tenant.products.index');
        Route::get('/products/search-catalog', [ProductController::class, 'searchCatalog'])->name('tenant.products.search-catalog');
        Route::post('/products/import-catalog', [ProductController::class, 'importFromCatalog'])->name('tenant.products.import-catalog');
        Route::post('/products/custom', [ProductController::class, 'storeCustom'])->name('tenant.products.custom');
        Route::put('/products/{id}', [ProductController::class, 'update'])->name('tenant.products.update');
        Route::delete('/products/{id}', [ProductController::class, 'destroy'])->name('tenant.products.destroy');
        Route::get('/products/{id}/print-barcode', [ProductController::class, 'printBarcode'])->name('tenant.products.print-barcode');

        // POS & Sales Management Routes
        Route::get('/pos', [PosController::class, 'index'])->name('tenant.pos.index');
        Route::get('/pos/search-products', [PosController::class, 'searchProducts'])->name('tenant.pos.search-products');
        Route::post('/pos/checkout', [PosController::class, 'checkout'])->name('tenant.pos.checkout');
        Route::get('/pos/invoice/{id}', [PosController::class, 'invoice'])->name('tenant.pos.invoice');
        Route::get('/pos/history', [PosController::class, 'history'])->name('tenant.pos.history');

        // Purchases & Stock In-flow Routes
        Route::get('/purchases', [PurchaseController::class, 'index'])->name('tenant.purchases.index');
        Route::get('/purchases/create', [PurchaseController::class, 'create'])->name('tenant.purchases.create');
        Route::post('/purchases', [PurchaseController::class, 'store'])->name('tenant.purchases.store');
        Route::get('/purchases/{id}', [PurchaseController::class, 'show'])->name('tenant.purchases.show');
        Route::delete('/purchases/{id}', [PurchaseController::class, 'destroy'])->name('tenant.purchases.destroy');

        // Store & Invoice Settings
        Route::get('/settings', [SettingController::class, 'index'])->name('tenant.settings.index');
        Route::put('/settings', [SettingController::class, 'update'])->name('tenant.settings.update');

        // Staff & Cashier Management
        Route::get('/staff', [StaffController::class, 'index'])->name('tenant.staff.index');
        Route::post('/staff', [StaffController::class, 'store'])->name('tenant.staff.store');
        Route::put('/staff/{id}', [StaffController::class, 'update'])->name('tenant.staff.update');
        Route::delete('/staff/{id}', [StaffController::class, 'destroy'])->name('tenant.staff.destroy');

        // Marketplace Commission & Due Statement
        Route::get('/commission', [\App\Http\Controllers\Tenant\CommissionController::class, 'index'])->name('tenant.commission.index');
        Route::post('/commission/pay', [\App\Http\Controllers\Tenant\CommissionController::class, 'pay'])->name('tenant.commission.pay');
    });

    // Alias /admin/pos to tenant POS
    Route::middleware(['auth:tenant_web'])->prefix('admin')->group(function () {
        Route::get('/products', [ProductController::class, 'index']);
        Route::get('/pos', [PosController::class, 'index']);
        Route::get('/pos/history', [PosController::class, 'history']);
    });
});

// =========================================================================
// 2. Tenant API Routes
// =========================================================================
Route::middleware([
    'api',
    InitializeTenancyByDomainOrSubdomain::class,
    PreventAccessFromCentralDomains::class,
    CheckTenantSubscription::class,
])->prefix('api/tenant')->name('tenant.api.')->group(function () {
    // Central catalog search & inspection
    Route::get('/catalog/search', [ApiTenantProductController::class, 'searchCentralCatalog'])->name('catalog.search');
    Route::get('/catalog/items/{id}', [ApiTenantProductController::class, 'showCentralItem'])->name('catalog.item');

    // Stock import actions
    Route::post('/catalog/import', [ApiTenantProductController::class, 'importGlobalItem'])->name('catalog.import');
    Route::post('/catalog/bulk-import', [ApiTenantProductController::class, 'bulkImport'])->name('catalog.bulk-import');

    // Local custom product actions
    Route::post('/products/custom', [ApiTenantProductController::class, 'createCustomProduct'])->name('products.custom');

    // Tenant inventory listing
    Route::get('/products', [ApiTenantProductController::class, 'index'])->name('products.index');
});
