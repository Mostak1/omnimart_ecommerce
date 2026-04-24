<?php

namespace App\Http\Controllers\Front;

use App\{
    Models\Item,
    Http\Controllers\Controller,
    Repositories\Front\CartRepository
};
use App\Helpers\PriceHelper;
use App\Models\ShippingService;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    /**
     * Constructor Method.
     *
     * @param  \App\Repositories\Front\CartRepository $repository
     *
     */
    public function __construct(CartRepository $repository)
    {
        $this->repository = $repository;
        $this->middleware('localize');
    }

    public function index()
    {
        if (Session::has('cart')) {
            $cart = Session::get('cart');
        } else {
            $cart = [];
        }
        return view('front.catalog.cart', [
            'cart' => $cart
        ]);
    }


    public function addToCart(Request $request)
    {

        $msg = $this->repository->store($request);


        if ($request->ajax()) {
            return $msg;
        }
    }

    public function store(Request $request)
    {

        $msg = $this->repository->store($request);
        if (isset($request->addtocart)) {
            Session::flash('success_message', __('Cart Added Successfully'));
            return back();
        }
        return redirect()->route('front.checkout.billing')->withSuccess($msg);
    }

    public function destroy($id)
    {

        $cart = Session::get('cart');
        unset($cart[$id]);
        if (count($cart) > 0) {
            Session::put('cart', $cart);
        } else {
            Session::forget('cart');
        }
        Session::flash('success', __('Cart item remove successfully.'));
        return back();
    }

    public function promoStore(Request $request)
    {
        $response = $this->repository->promoStore($request);

        if (! $response['status']) {
            return response()->json($response);
        }

        return response()->json(array_merge($response, $this->checkoutSummaryResponse($request)));
    }

    public function shippingStore(Request $request)
    {
        return redirect()->route('front.checkout');
    }


    public function update($id)
    {
        return view('front.catalog.cart_form', [
            'item' => Item::findOrFail($id),
            'attributes' => Item::findOrFail($id)->attributes,
            'cart_item' => Session::get('cart')[$id],
        ]);
    }


    public function shippingCharge(Request $request)
    {

        $charges = [];
        $items = [];
        foreach ($request->user_id as $data) {
            $check = explode('|', $data);
            $charges[] = $check[0];
            $items[] = $check[1];
        }
        $cart = Session::get('cart');
        $delivery_amount = 0;
        foreach ($charges as $index => $charge) {
            if ($charge != 0) {
                $vendor_charge = Item::findOrFail($items[$index])->user->shipping->price;
                $delivery_amount += $vendor_charge;
                $cart[$items[$index]]['delivery_charge'] = $vendor_charge;
            } else {
                $cart[$items[$index]]['delivery_charge'] = 0;
            }
        }

        Session::put('cart', $cart);

        return response()->json(['delivery' => PriceHelper::setPrice($delivery_amount), 'main' => $delivery_amount]);
    }


    public function headerCartLoad()
    {
        return view('includes.header_cart');
    }
    public function CartLoad()
    {
        return view('includes.cart');
    }

    public function cartClear()
    {
        Session::forget('cart');
        Session::flash('success', __('Cart clear successfully'));
        return back();
    }

    public function promoDelete()
    {
        Session::forget('coupon');

        if (request()->ajax()) {
            return response()->json([
                'status' => true,
                'message' => __('Promo code remove successfully'),
            ] + $this->checkoutSummaryResponse(request()));
        }

        Session::flash('success', __('Promo code remove successfully'));
        return back();
    }

    private function checkoutSummaryResponse(Request $request): array
    {
        $cart = Session::get('cart', []);
        $cartTotal = 0;
        $totalTax = 0;

        foreach ($cart as $key => $items) {
            $cartTotal += ($items['main_price'] + $items['attribute_price']) * $items['qty'];
            $item = Item::find(PriceHelper::GetItemId($key));

            if ($item && $item->tax) {
                $totalTax += $item::taxCalculate($item) * $items['qty'];
            }
        }

        $district = $request->input('district')
            ?: Session::get('shipping_address.ship_country')
            ?: Session::get('billing_address.bill_country')
            ?: (Auth::check() ? (Auth::user()->ship_country ?: Auth::user()->bill_country) : null);

        $shipping = PriceHelper::Digital() ? ShippingService::appliedService($district, $cart) : null;
        $discount = Session::get('coupon');

        $grandTotal = $cartTotal + ($shipping ? $shipping->price : 0) + $totalTax;
        $grandTotal -= $discount['discount'] ?? 0;

        $statePrice = 0;
        $stateId = $request->input('state_id');

        if ($stateId) {
            $state = State::find($stateId);
            if ($state) {
                $statePrice = $state->type === 'fixed'
                    ? $state->price
                    : ($cartTotal * $state->price) / 100;
            }
        } elseif (Auth::check() && Auth::user()->state_id) {
            $state = Auth::user()->state;
            if ($state) {
                $statePrice = $state->type === 'fixed'
                    ? $state->price
                    : ($cartTotal * $state->price) / 100;
            }
        }

        $grandTotal += $statePrice;

        return [
            'discount_price' => PriceHelper::setCurrencyPrice($discount['discount'] ?? 0),
            'discount_name' => $discount['code']['title'] ?? '',
            'grand_total' => PriceHelper::setCurrencyPrice($grandTotal),
            'shipping_price' => PriceHelper::setCurrencyPrice($shipping ? $shipping->price : 0),
        ];
    }
}
