<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\CreateCustomItemAction;
use App\Helpers\BarcodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Services\CentralCatalogService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class ProductController extends Controller
{
    public function __construct(
        protected CentralCatalogService $centralCatalogService,
        protected CreateCustomItemAction $createCustomItemAction
    ) {}

    /**
     * Store owner product list view with statistics and stock alerts.
     */
    public function index(Request $request): View
    {
        $query = Product::query();

        // Search by keyword or barcode
        if ($term = trim((string)$request->query('q', ''))) {
            $query->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('barcode', 'LIKE', "%{$term}%");
            });
        }

        // Filter by product type
        $type = $request->query('type');
        if ($type === 'custom') {
            $query->custom();
        } elseif ($type === 'imported') {
            $query->imported();
        }

        // Filter by stock alert
        if ($request->query('filter') === 'low_stock' || $request->boolean('low_stock')) {
            $query->lowStock();
        } elseif ($request->query('filter') === 'out_of_stock') {
            $query->where('stock_quantity', '<=', 0);
        }

        // Inventory Statistics
        $totalProducts = Product::count();
        $customProducts = Product::custom()->count();
        $importedProducts = Product::imported()->count();
        $lowStockCount = Product::lowStock()->count();
        $outOfStockCount = Product::where('stock_quantity', '<=', 0)->count();

        $products = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('tenant.products.index', compact(
            'products',
            'totalProducts',
            'customProducts',
            'importedProducts',
            'lowStockCount',
            'outOfStockCount'
        ));
    }

    /**
     * AJAX search in the central catalog from the modal.
     */
    public function searchCatalog(Request $request): JsonResponse
    {
        $term = (string)$request->query('q', '');
        $categoryId = $request->filled('category_id') ? (int)$request->query('category_id') : null;

        try {
            $paginator = $this->centralCatalogService->search($term, $categoryId, 12);

            return response()->json([
                'success' => true,
                'items' => $paginator->items(),
                'total' => $paginator->total(),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'ক্যাটালগ সার্চ ব্যর্থ হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * AJAX action to import a central catalog item into the store's inventory.
     */
    public function importFromCatalog(Request $request): JsonResponse
    {
        $request->validate([
            'item_id' => 'required|integer',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
        ]);

        try {
            $product = $this->centralCatalogService->importToTenant(
                (int)$request->input('item_id'),
                $request->all()
            );

            return response()->json([
                'success' => true,
                'message' => "‘{$product->name}’ সফলভাবে আপনার দোকানে যুক্ত হয়েছে!",
                'product' => $product,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'পণ্য ইমপোর্ট করতে সমস্যা হয়েছে: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new custom local product.
     */
    public function storeCustom(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
        ]);

        try {
            $data = $request->all();
            if (empty($data['barcode'])) {
                // Auto generate barcode if none provided
                $data['barcode'] = 'LOC' . str_pad((string)(Product::count() + 1), 6, '0', STR_PAD_LEFT);
            }

            $product = $this->createCustomItemAction->execute($data);

            return redirect()->route('tenant.products.index')
                ->with('success', "নতুন লোকাল পণ্য ‘{$product->name}’ সফলভাবে তৈরি হয়েছে!");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'পণ্য তৈরি ব্যর্থ হয়েছে: ' . $e->getMessage());
        }
    }

    /**
     * Update an existing product's price, stock, and details.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'barcode' => 'nullable|string|max:100',
            'retail_price' => 'required|numeric|min:0',
            'cost_price' => 'nullable|numeric|min:0',
            'wholesale_price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'required|numeric|min:0',
            'alert_quantity' => 'nullable|numeric|min:0',
            'unit' => 'nullable|string|max:20',
        ]);

        $product->update([
            'name' => $request->input('name'),
            'barcode' => $request->input('barcode') ?: $product->barcode,
            'retail_price' => $request->input('retail_price'),
            'cost_price' => $request->input('cost_price', 0),
            'wholesale_price' => $request->input('wholesale_price', 0),
            'stock_quantity' => $request->input('stock_quantity'),
            'alert_quantity' => $request->input('alert_quantity', 5),
            'unit' => $request->input('unit', 'pcs'),
            'status' => $request->has('status') ? (bool)$request->input('status') : true,
        ]);

        return redirect()->route('tenant.products.index')
            ->with('success', "‘{$product->name}’ এর তথ্য সফলভাবে আপডেট হয়েছে!");
    }

    /**
     * Delete a product from the local store.
     */
    public function destroy(int $id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        $name = $product->name;
        $product->delete();

        return redirect()->route('tenant.products.index')
            ->with('success', "‘{$name}’ তালিকা থেকে মুছে ফেলা হয়েছে।");
    }

    /**
     * Render printable barcode labels with pure SVG barcode.
     */
    public function printBarcode(Request $request, int $id): View
    {
        $product = Product::findOrFail($id);
        $copies = max(1, min((int)$request->query('qty', 12), 100));

        $barcodeCode = $product->barcode ?: ('SKU' . str_pad((string)$product->id, 6, '0', STR_PAD_LEFT));
        $barcodeSvg = BarcodeGenerator::svg($barcodeCode, 45, 2, false);

        return view('tenant.products.print-barcode', compact('product', 'copies', 'barcodeCode', 'barcodeSvg'));
    }
}

