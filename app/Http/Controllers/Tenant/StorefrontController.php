<?php

namespace App\Http\Controllers\Tenant;

use App\Actions\ProcessPosSaleAction;
use App\Http\Controllers\Controller;
use App\Models\Tenant\Product;
use App\Models\Tenant\Sale;
use App\Models\Tenant\TenantSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Throwable;

class StorefrontController extends Controller
{
    public function __construct(
        protected ProcessPosSaleAction $processPosSaleAction
    ) {}

    /**
     * Tenant public eCommerce storefront homepage.
     */
    public function index(Request $request): View
    {
        $query = Product::where('status', true);

        if ($search = trim((string)$request->query('q', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('barcode', 'LIKE', "%{$search}%");
            });
        }

        $products = $query->orderBy('id', 'desc')->paginate(12)->withQueryString();
        $settings = TenantSetting::getAll();

        return view('tenant.storefront.index', compact('products', 'settings'));
    }

    /**
     * Customer online checkout (Cash on Delivery / COD).
     */
    public function placeOrder(Request $request): JsonResponse
    {
        $request->validate([
            'customer_name' => 'required|string|max:100',
            'customer_phone' => 'required|string|max:20',
            'delivery_address' => 'required|string|max:300',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|integer',
            'items.*.quantity' => 'required|numeric|min:0.01',
        ], [
            'customer_name.required' => 'আপনার নাম প্রদান করুন।',
            'customer_phone.required' => 'মোবাইল নম্বর প্রদান করুন।',
            'delivery_address.required' => 'ডেলিভারি ঠিকানা প্রদান করুন।',
            'items.required' => 'কার্টে কোনো পণ্য নেই।',
        ]);

        try {
            // Prepare items with current product price
            $preparedItems = [];
            foreach ($request->input('items') as $cartItem) {
                $p = Product::findOrFail($cartItem['product_id']);
                $preparedItems[] = [
                    'product_id' => $p->id,
                    'name' => $p->name,
                    'unit_price' => (float)$p->retail_price,
                    'quantity' => (float)$cartItem['quantity'],
                ];
            }

            $payload = [
                'items' => $preparedItems,
                'customer_name' => $request->input('customer_name'),
                'customer_phone' => $request->input('customer_phone'),
                'discount_amount' => 0,
                'tax_amount' => 0,
                'paid_amount' => 0, // COD: Paid on delivery
                'payment_method' => 'cash',
                'notes' => 'অনলাইন অর্ডার (COD) | ঠিকানা: ' . $request->input('delivery_address') . ($request->filled('notes') ? ' | নোট: ' . $request->input('notes') : ''),
            ];

            $sale = $this->processPosSaleAction->execute($payload, null);

            return response()->json([
                'success' => true,
                'message' => 'আপনার অর্ডারটি সফলভাবে গৃহীত হয়েছে!',
                'redirect_url' => route('tenant.storefront.order-success', $sale->invoice_no),
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'অর্ডার করতে সমস্যা হয়েছে: ' . $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Customer order confirmation page.
     */
    public function orderSuccess(string $invoiceNo): View|RedirectResponse
    {
        $sale = Sale::with('items')->where('invoice_no', $invoiceNo)->firstOrFail();
        $settings = TenantSetting::getAll();

        return view('tenant.storefront.success', compact('sale', 'settings'));
    }
}
