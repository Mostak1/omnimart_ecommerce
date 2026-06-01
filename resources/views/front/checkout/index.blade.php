@extends('master.front')

@section('title')
    {{ __('Billing') }}
@endsection

@section('content')
    <!-- Page Title-->
    @if (data_get($site_visibility, 'checkout_breadcrumb', 1))
        <div class="page-title">
            <div class="container">
                <div class="column">
                    <ul class="breadcrumbs">
                        <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a> </li>
                        <li class="separator"></li>
                        <li>{{ __('Billing address') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <!-- Page Content-->
    <div class="container padding-bottom-3x mb-1 checkut-page">
        <div class="row">
            @if (data_get($site_visibility, 'checkout_billing_form', 1))
                <div class="col-xl-8 col-lg-8">
                    <div class="row">
                        <div class="col-12">
                            <section class="card widget widget-featured-posts widget-featured-products p-4">
                                <h3 class="widget-title">{{ __('Items In Your Cart') }}</h3>
                                @foreach ($cart as $key => $item)
                                    <div class="entry">
                                        <div class="entry-thumb"><a href="{{ route('front.product', $item['slug']) }}"><img
                                                    src="{{ url('/storage/images/' . $item['photo']) }}" alt="Product"></a>
                                        </div>
                                        <div class="entry-content">
                                            <h4 class="entry-title"><a href="{{ route('front.product', $item['slug']) }}">
                                                    {{ Str::limit($item['name'], 45) }}

                                                </a></h4>
                                            <span class="entry-meta">{{ $item['qty'] }} x

                                                @php
                                                    $totalAttributePrice = 0;
                                                    foreach ($item['attribute']['option_price'] as $option_price) {
                                                        $totalAttributePrice += $option_price;
                                                    }
                                                    $price = $item['main_price'] + $totalAttributePrice;
                                                @endphp
                                                {{ PriceHelper::setCurrencyPrice($price) }}.</span>

                                            @foreach ($item['attribute']['option_name'] as $optionkey => $option_name)
                                                <div class="entry-meta">
                                                    <span
                                                        class="entry-meta d-inline">{{ $item['attribute']['names'][$optionkey] }}:</span>
                                                    <span class="entry-meta d-inline"><b>{{ $option_name }}</b></span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </section>
                            <div class="card">
                                <div class="card-body">
                                    <h6>{{ __('Billing Address') }}</h6>
                                    <form id="checkoutBilling" action="{{ route('front.checkout.submit') }}"
                                        method="POST">
                                        @csrf
                                        <input type="hidden" name="single_page_checkout" value="1">
                                        <input type="hidden" name="payment_method" id="checkout_payment_method"
                                            value="">
                                        <input type="hidden" name="state_id" id="checkout_state_id"
                                            value="{{ old('state_id') }}">
                                        <input type="hidden" name="shipping_id" id="checkout_shipping_id"
                                            value="{{ isset($shipping) && $shipping ? $shipping->id : '' }}">
                                        <div class="row">
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="checkout-fn">{{ __('Name') }}*</label>
                                                    <input
                                                        class="form-control {{ $errors->has('bill_first_name') ? 'requireInput' : '' }}"
                                                        name="bill_first_name" type="text" id="checkout-fn"
                                                        value="{{ isset($user) ? $user->first_name : '' }}">
                                                </div>
                                            </div>
                                            <div class="col-sm-12">
                                                <div class="form-group">
                                                    <label for="checkout-phone">{{ __('Phone Number') }}*</label>
                                                    <input
                                                        class="form-control {{ $errors->has('bill_phone') ? 'requireInput' : '' }}"
                                                        name="bill_phone" type="text" id="checkout-phone"
                                                        value="{{ isset($user) ? $user->phone : '' }}">
                                                </div>
                                            </div>
                                        </div>
                                        @if (PriceHelper::CheckDigital())
                                            <div class="row">
                                                <div class="col-sm-12">
                                                    <div class="form-group">
                                                        <label for="checkout-address1">{{ __('Address') }}*</label>
                                                        <input
                                                            class="form-control {{ $errors->has('bill_address1') ? 'requireInput' : '' }}"
                                                            name="bill_address1" type="text" id="checkout-address1"
                                                            value="{{ isset($user) ? $user->bill_address1 : '' }}">
                                                    </div>
                                                </div>
                                            </div>
                                            @if (PriceHelper::checkoutDistrictEnabled())
                                                <div class="row">
                                                    <div
                                                        class="col-sm-{{ PriceHelper::checkoutPoliceStationEnabled() ? '6' : '12' }}">
                                                        <div class="form-group">
                                                            <label
                                                                for="billing-country">{{ __('District') }}{{ PriceHelper::checkoutDistrictRequired() ? '*' : '' }}</label>
                                                            <select
                                                                class="form-control {{ $errors->has('bill_country') ? 'requireInput' : '' }}"
                                                                name="bill_country" id="billing-country"
                                                                {{ PriceHelper::checkoutDistrictRequired() ? 'required' : '' }}
                                                                data-shipping-url="{{ route('front.shipping.setup') }}"
                                                                data-police-stations-url="{{ url('/get-police-stations') }}">
                                                                <option value="" selected disabled>
                                                                    {{ __('Choose District') }}</option>
                                                                @foreach ($districts as $district)
                                                                    <option value="{{ $district->name }}"
                                                                        {{ isset($user) && $user->bill_country == $district->name ? 'selected' : '' }}>
                                                                        {{ $district->name }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                    </div>
                                                    @if (PriceHelper::checkoutPoliceStationEnabled())
                                                        <div class="col-sm-6">
                                                            <div class="form-group">
                                                                <label
                                                                    for="checkout-thana">{{ __('Police Station') }}{{ PriceHelper::checkoutPoliceStationRequired() ? '*' : '' }}</label>
                                                                <select
                                                                    class="form-control {{ $errors->has('bill_thana') ? 'requireInput' : '' }}"
                                                                    name="bill_thana" id="checkout-thana"
                                                                    {{ PriceHelper::checkoutPoliceStationRequired() ? 'required' : '' }}>
                                                                    <option value="" selected disabled>
                                                                        {{ __('Select Police Station') }}</option>
                                                                    @if (isset($user) && $user->bill_thana)
                                                                        <option value="{{ $user->bill_thana }}" selected>
                                                                            {{ $user->bill_thana }}</option>
                                                                    @endif
                                                                </select>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        @endif
                                    </form>
                                    @if (data_get($site_visibility, 'checkout_order_summary', 1))
                                        <div class="card mt-4">
                                            <div class="card-body">
                                                <h6>{{ __('Coupon') }}</h6>
                                                <form method="post" id="checkout_coupon_form"
                                                    action="{{ route('front.promo.submit') }}">
                                                    @csrf
                                                    <div class="form-group mb-2">
                                                        <input class="form-control form-control-sm" name="code"
                                                            type="text" placeholder="{{ __('Coupon code') }}" required>
                                                    </div>
                                                    <button class="btn btn-primary btn-sm"
                                                        type="submit"><span>{{ __('Apply Coupon') }}</span></button>
                                                    <p
                                                        class="small text-success mt-2 mb-0 checkout_coupon_name {{ $discount ? '' : 'd-none' }}">
                                                        {{ $discount ? $discount['code']['title'] : '' }}
                                                    </p>
                                                </form>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <!-- Sidebar          -->
            @if (data_get($site_visibility, 'checkout_order_summary', 1) ||
                    data_get($site_visibility, 'checkout_payment_methods', 1))
                <div class="col-xl-4 col-lg-4">
                    @include('includes.single_checkout_sidebar', $cart)
                    @include('includes.single_checkout_modal')
                </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    @php
        $beginCheckoutItems = [];

        foreach ($cart as $key => $items) {
            $itemId = \App\Helpers\PriceHelper::GetItemId($key);
            $item = \App\Models\Item::with(['brand', 'category'])->find($itemId);

            $beginCheckoutItems[] = [
                'item_id' => (string) $itemId,
                'item_name' => (string) data_get($items, 'name', ''),
                'item_brand' => $item && $item->brand ? $item->brand->name : '',
                'item_category' => $item && $item->category ? $item->category->name : '',
                'price' => (float) data_get($items, 'main_price', 0) + (float) data_get($items, 'attribute_price', 0),
                'quantity' => (int) data_get($items, 'qty', 0),
            ];
        }

        $beginCheckoutPayload = [
            'event' => 'begin_checkout',
            'ecommerce' => [
                'currency' => PriceHelper::getCurrencyCode(),
                'value' => (float) $cart_total,
                'items' => $beginCheckoutItems,
            ],
        ];
    @endphp

    <script>
        // GTM begin_checkout GA4 eCommerce
        if (typeof window.omnimartPushBeginCheckout === 'function') {
            window.omnimartPushBeginCheckout(@json($beginCheckoutPayload), {
                skipRecentClick: true
            });
        } else {
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({ ecommerce: null });
            window.dataLayer.push(@json($beginCheckoutPayload));
        }
    </script>
@endsection
