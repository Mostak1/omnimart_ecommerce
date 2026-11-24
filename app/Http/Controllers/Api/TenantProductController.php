<?php

namespace App\Http\Controllers\Api;

use App\Actions\CreateCustomItemAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Tenant\Product;
use App\Services\CentralCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Throwable;

class TenantProductController extends Controller
{
    public function __construct(
        protected CentralCatalogService $centralCatalogService,
        protected CreateCustomItemAction $createCustomItemAction
    ) {
    }

    /**
     * Fallback to initialize tenancy if accessed via central API with X-Tenant header or tenant parameter.
     */
    protected function ensureTenantInitialized(): void
    {
        if (function_exists('tenancy') && !tenancy()->initialized) {
            $request = request();
            $tenantIdentifier = $request->header('X-Tenant')
                ?? $request->header('X-Tenant-Id')
                ?? $request->input('tenant_id')
                ?? $request->input('tenant');

            if ($tenantIdentifier) {
                $tenant = Tenant::find($tenantIdentifier);
                if ($tenant) {
                    tenancy()->initialize($tenant);
                }
            }
        }
    }

    /**
     * Search the central items catalog and check current tenant inventory status.
     *
     * GET /api/tenant/catalog/search
     * Params: q (string), category_id (int), per_page (int)
     */
    public function searchCentralCatalog(Request $request): JsonResponse
    {
        $this->ensureTenantInitialized();

        $query = (string)$request->query('q', '');
        $categoryId = $request->filled('category_id') ? (int)$request->query('category_id') : null;
        $perPage = min((int)$request->query('per_page', 15), 100);

        try {
            $results = $this->centralCatalogService->search($query, $categoryId, $perPage);

            return response()->json([
                'success' => true,
                'data' => $results->items(),
                'pagination' => [
                    'total' => $results->total(),
                    'per_page' => $results->perPage(),
                    'current_page' => $results->currentPage(),
                    'last_page' => $results->lastPage(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to search central catalog: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * View a specific central catalog item along with local stock information.
     *
     * GET /api/tenant/catalog/items/{id}
     */
    public function showCentralItem(Request $request, int $id): JsonResponse
    {
        $this->ensureTenantInitialized();

        try {
            $item = $this->centralCatalogService->findCentralItem($id);

            return response()->json([
                'success' => true,
                'data' => $item,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Central item not found: ' . $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Import a central catalog item into the tenant's inventory.
     *
     * POST /api/tenant/catalog/import
     * Body: item_id, retail_price, cost_price, wholesale_price, stock_quantity, alert_quantity, unit, barcode, add_to_stock
     */
    public function importGlobalItem(Request $request): JsonResponse
    {
        $this->ensureTenantInitialized();

        if (function_exists('tenancy') && !tenancy()->initialized) {
            return response()->json([
                'success' => false,
                'message' => 'Tenancy context is required. Please access via store subdomain or pass X-Tenant header.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'item_id' => 'required|integer',
            'retail_price' => 'nullable|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'barcode' => 'nullable|string|max:100',
            'name' => 'nullable|string|max:255',
            'add_to_stock' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = $this->centralCatalogService->importToTenant(
                (int)$request->input('item_id'),
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => 'Central catalog item successfully imported into store inventory.',
                'data' => $product,
            ], 200);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk import multiple items into tenant stock.
     *
     * POST /api/tenant/catalog/bulk-import
     * Body: items: [{ item_id, retail_price, cost_price, stock_quantity }, ...]
     */
    public function bulkImport(Request $request): JsonResponse
    {
        $this->ensureTenantInitialized();

        if (function_exists('tenancy') && !tenancy()->initialized) {
            return response()->json([
                'success' => false,
                'message' => 'Tenancy context is required.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|integer',
            'items.*.retail_price' => 'nullable|numeric|min:0',
            'items.*.cost_price' => 'nullable|numeric|min:0',
            'items.*.stock_quantity' => 'nullable|numeric|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->centralCatalogService->bulkImportToTenant($request->input('items'));

            return response()->json([
                'success' => true,
                'message' => "Bulk import completed. Successfully imported {$result['success_count']} items.",
                'data' => $result,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bulk import failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a custom local product for products not found in central catalog.
     *
     * POST /api/tenant/products/custom
     */
    public function createCustomProduct(Request $request): JsonResponse
    {
        $this->ensureTenantInitialized();

        if (function_exists('tenancy') && !tenancy()->initialized) {
            return response()->json([
                'success' => false,
                'message' => 'Tenancy context is required.',
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:50',
            'photo' => 'nullable|string|max:255',
            'status' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $product = $this->createCustomItemAction->execute($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Custom local product created successfully.',
                'data' => $product,
            ], 201);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create custom product: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * List all products in the current tenant store.
     *
     * GET /api/tenant/products
     * Params: q, type (all|custom|imported), low_stock (bool), per_page (int)
     */
    public function index(Request $request): JsonResponse
    {
        $this->ensureTenantInitialized();

        if (function_exists('tenancy') && !tenancy()->initialized) {
            return response()->json([
                'success' => false,
                'message' => 'Tenancy context is required.',
            ], 400);
        }

        try {
            $query = Product::query();

            // Search by name or barcode
            if ($term = trim((string)$request->query('q', ''))) {
                $query->where(function ($q) use ($term) {
                    $q->where('name', 'LIKE', "%{$term}%")
                      ->orWhere('barcode', 'LIKE', "%{$term}%");
                });
            }

            // Filter by type: custom vs imported
            $type = $request->query('type');
            if ($type === 'custom') {
                $query->custom();
            } elseif ($type === 'imported') {
                $query->imported();
            }

            // Filter by low stock
            if ($request->boolean('low_stock')) {
                $query->lowStock();
            }

            $perPage = min((int)$request->query('per_page', 20), 100);
            $products = $query->orderBy('id', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $products->items(),
                'pagination' => [
                    'total' => $products->total(),
                    'per_page' => $products->perPage(),
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                ],
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve products: ' . $e->getMessage(),
            ], 500);
        }
    }
}
