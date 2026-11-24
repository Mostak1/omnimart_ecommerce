<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ProcessPurchaseAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Purchase;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Throwable;

class PurchaseController extends Controller
{
    public function __construct(
        protected ProcessPurchaseAction $processPurchaseAction
    ) {}

    /**
     * List all wholesale purchases & vendor invoices.
     */
    public function index(Request $request): View
    {
        $query = Purchase::with(['items', 'creator']);

        if ($search = trim((string)$request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_no', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_name', 'LIKE', "%{$search}%")
                  ->orWhere('supplier_phone', 'LIKE', "%{$search}%");
            });
        }

        if ($status = $request->query('payment_status')) {
            $query->where('payment_status', $status);
        }

        $totalPurchasesAmount = (float)Purchase::sum('total_amount');
        $totalPaidAmount = (float)Purchase::sum('paid_amount');
        $totalDueAmount = (float)Purchase::sum('due_amount');
        $totalPurchasesCount = Purchase::count();

        $purchases = $query->orderBy('id', 'desc')->paginate(15)->withQueryString();

        return view('tenant.purchases.index', compact(
            'purchases',
            'totalPurchasesAmount',
            'totalPaidAmount',
            'totalDueAmount',
            'totalPurchasesCount'
        ));
    }

    /**
     * Show form to create a new purchase invoice.
     */
    public function create(): View
    {
        $products = Product::where('status', true)->orderBy('name', 'asc')->get();
        return view('tenant.purchases.create', compact('products'));
    }

    /**
     * Store a new purchase invoice and increment stock.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'supplier_name' => 'required|string|max:150',
            'supplier_phone' => 'nullable|string|max:25',
            'invoice_no' => 'nullable|string|max:100',
            'purchase_date' => 'nullable|date',
            'paid_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ]);

        try {
            $user = Auth::guard('tenant_web')->user();
            $purchase = $this->processPurchaseAction->execute($request->all(), $user?->id);

            return redirect()->route('tenant.purchases.index')
                ->with('success', "চালান #{$purchase->invoice_no} সফলভাবে সংরক্ষিত হয়েছে এবং স্টক বৃদ্ধি পেয়েছে!");
        } catch (Throwable $e) {
            return back()->withInput()->with('error', 'চালান সংরক্ষণ ব্যর্থ: ' . $e->getMessage());
        }
    }

    /**
     * View purchase bill details.
     */
    public function show(int $id): View
    {
        $purchase = Purchase::with(['items.product', 'creator'])->findOrFail($id);
        return view('tenant.purchases.show', compact('purchase'));
    }

    /**
     * Cancel/delete a purchase and revert stock.
     */
    public function destroy(int $id): RedirectResponse
    {
        $purchase = Purchase::with('items')->findOrFail($id);

        foreach ($purchase->items as $item) {
            if ($item->product_id) {
                Product::where('id', $item->product_id)->decrement('stock_quantity', $item->quantity);
            }
        }

        $inv = $purchase->invoice_no;
        $purchase->delete();

        return redirect()->route('tenant.purchases.index')
            ->with('success', "চালান #{$inv} বাতিল করা হয়েছে এবং স্টক সমন্বয় করা হয়েছে।");
    }
}
