<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ProcessPosSaleAction;
use App\Helpers\BarcodeGenerator;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PosController extends Controller
{
    public function __construct(
        protected ProcessPosSaleAction $processPosSaleAction
    ) {}

    /**
     * Show POS Billing Terminal.
     */
    public function index(): View
    {
        $products = Product::where('status', true)
            ->orderBy('name', 'asc')
            ->take(30)
            ->get();

        $todaySalesCount = Sale::whereDate('created_at', today())->count();
        $todaySalesAmount = (float)Sale::whereDate('created_at', today())->sum('total_amount');

        return view('tenant.pos.index', compact('products', 'todaySalesCount', 'todaySalesAmount'));
    }

    /**
     * Search products for POS by barcode or keyword.
     */
    public function searchProducts(Request $request): JsonResponse
    {
        $term = trim((string)$request->query('q', ''));
        if ($term === '') {
            return response()->json(['items' => []]);
        }

        // 1. Exact barcode match check (for instant barcode scanner add)
        $exactBarcode = Product::where('barcode', $term)
            ->where('status', true)
            ->first();

        if ($exactBarcode) {
            return response()->json([
                'exact_match' => true,
                'items' => [$exactBarcode],
            ]);
        }

        // 2. Keyword search
        $items = Product::where('status', true)
            ->where(function ($q) use ($term) {
                $q->where('name', 'LIKE', "%{$term}%")
                  ->orWhere('barcode', 'LIKE', "%{$term}%");
            })
            ->take(15)
            ->get();

        return response()->json([
            'exact_match' => false,
            'items' => $items,
        ]);
    }

    /**
     * Checkout cart and complete sale.
     */
    public function checkout(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'nullable|integer',
            'items.*.name' => 'required|string',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'customer_name' => 'nullable|string|max:100',
            'customer_phone' => 'nullable|string|max:25',
            'discount_amount' => 'nullable|numeric|min:0',
            'tax_amount' => 'nullable|numeric|min:0',
            'paid_amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|in:cash,bkash,nagad,card,due',
            'notes' => 'nullable|string|max:500',
        ]);

        try {
            $user = Auth::guard('tenant_web')->user();
            $sale = $this->processPosSaleAction->execute($request->all(), $user?->id);

            return response()->json([
                'success' => true,
                'message' => 'বিক্রয় সফলভাবে সম্পন্ন হয়েছে!',
                'sale_id' => $sale->id,
                'invoice_no' => $sale->invoice_no,
                'invoice_url' => route('tenant.pos.invoice', $sale->id),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'বিক্রয় সম্পন্ন করতে সমস্যা হয়েছে: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Printable thermal cash memo / invoice receipt.
     */
    public function invoice(int $id): View
    {
        $sale = Sale::with(['items', 'creator'])->findOrFail($id);
        $barcodeSvg = BarcodeGenerator::svg($sale->invoice_no, 38, 2, false);

        return view('tenant.pos.invoice', compact('sale', 'barcodeSvg'));
    }

    /**
     * Sales history list and reports.
     */
    public function history(Request $request): View
    {
        $query = Sale::with(['items', 'creator']);

        // Search by invoice or customer phone
        if ($search = trim((string)$request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('customer_phone', 'LIKE', "%{$search}%")
                  ->orWhere('customer_name', 'LIKE', "%{$search}%");
            });
        }

        // Filter by payment method
        if ($method = $request->query('payment_method')) {
            $query->where('payment_method', $method);
        }

        // Filter by payment status
        if ($status = $request->query('payment_status')) {
            $query->where('payment_status', $status);
        }

        // Date filter
        if ($date = $request->query('date')) {
            if ($date === 'today') {
                $query->whereDate('created_at', today());
            } elseif ($date === 'this_month') {
                $query->whereMonth('created_at', now()->month)
                      ->whereYear('created_at', now()->year);
            }
        }

        // Statistics
        $todayTotal = (float)Sale::whereDate('created_at', today())->sum('total_amount');
        $overallTotal = (float)Sale::sum('total_amount');
        $totalDue = (float)Sale::sum('due_amount');
        $totalSalesCount = Sale::count();

        $sales = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('tenant.pos.history', compact(
            'sales',
            'todayTotal',
            'overallTotal',
            'totalDue',
            'totalSalesCount'
        ));
    }
}
