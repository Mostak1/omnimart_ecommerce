<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// Tenant management API routes (supports X-Tenant header or tenant_id query/body param)
Route::prefix('tenant')->name('api.tenant.')->group(function () {
    Route::get('/catalog/search', [\App\Http\Controllers\Api\TenantProductController::class, 'searchCentralCatalog']);
    Route::get('/catalog/items/{id}', [\App\Http\Controllers\Api\TenantProductController::class, 'showCentralItem']);
    Route::post('/catalog/import', [\App\Http\Controllers\Api\TenantProductController::class, 'importGlobalItem']);
    Route::post('/catalog/bulk-import', [\App\Http\Controllers\Api\TenantProductController::class, 'bulkImport']);
    Route::post('/products/custom', [\App\Http\Controllers\Api\TenantProductController::class, 'createCustomProduct']);
    Route::get('/products', [\App\Http\Controllers\Api\TenantProductController::class, 'index']);
});



