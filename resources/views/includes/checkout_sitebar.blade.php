<aside class="sidebar">
    <div class="padding-top-2x hidden-lg-up"></div>
    <!-- Items in Cart Widget-->


    <section class="card widget widget-featured-posts widget-order-summary p-4">
        <h3 class="widget-title">{{ __('Order Summary') }}</h3>
        @php
            $free_shipping = DB::table('shipping_services')->whereStatus(1)->whereIsCondition(1)->first();
        @endphp

        @if ($free_shipping)
            @if ($free_shipping->minimum_price >= $cart_total)
                <p class="free-shippin-aa"><em>{{ __('Free Shipping After Order') }}
                        {{ PriceHelper::setCurrencyPrice($free_shipping->minimum_price) }}</em></p>
            @endif
        @endif

        <table class="table">
            <tr>
                <td>{{ __('Cart subtotal') }}:</td>
                <td class="text-gray-dark">{{ PriceHelper::setCurrencyPrice($cart_total) }}</td>
            </tr>

            <tr class="checkout_coupon_row {{ $discount ? '' : 'd-none' }}">
                <td>{{ __('Coupon discount') }}:</td>
                <td class="text-danger">-
                    <span class="checkout_coupon_amount">{{ PriceHelper::setCurrencyPrice($discount ? $discount['discount'] : 0) }}</span>
                    <a href="{{ route('front.promo.destroy') }}" class="btn btn-danger btn-sm ml-2 remove-checkout-coupon" title="{{ __('Remove coupon') }}">
                        <i class="icon-x"></i>
                    </a>
                </td>
            </tr>

            @if ($shipping || PriceHelper::CheckDigital())
                <tr class="{{ $shipping ? '' : 'd-none' }} set__shipping_price_tr">
                    <td>{{ __('Shipping') }}:</td>
                    <td class="text-gray-dark set__shipping_price">
                        {{ PriceHelper::setCurrencyPrice($shipping ? $shipping->price : 0) }}</td>
                </tr>
            @endif
            <tr>
                <td class="text-lg text-primary">{{ __('Order total') }}</td>
                <td class="text-lg text-primary grand_total_set">{{ PriceHelper::setCurrencyPrice($grand_total) }}
                </td>
            </tr>
        </table>
    </section>

    <section class="card widget widget-featured-posts widget-order-summary p-4">
        <h3 class="widget-title">{{ __('Coupon') }}</h3>
        <form method="post" id="checkout_coupon_form" action="{{ route('front.promo.submit') }}">
            @csrf
            <div class="form-group mb-2">
                <input class="form-control form-control-sm" name="code" type="text"
                    placeholder="{{ __('Coupon code') }}" required>
            </div>
            <button class="btn btn-primary btn-sm" type="submit"><span>{{ __('Apply Coupon') }}</span></button>
            <p class="small text-success mt-2 mb-0 checkout_coupon_name {{ $discount ? '' : 'd-none' }}">
                {{ $discount ? $discount['code']['title'] : '' }}
            </p>
        </form>
    </section>


    <section class="card widget widget-featured-posts widget-featured-products p-4">
        <h3 class="widget-title">{{ __('Items In Your Cart') }}</h3>
        @foreach ($cart as $key => $item)
            <div class="entry">
                <div class="entry-thumb"><a href="{{ route('front.product', $item['slug']) }}"><img
                            src="{{ url('/storage/images/' . $item['photo']) }}" alt="Product"></a>
                </div>
                <div class="entry-content">
                    <h4 class="entry-title"><a href="{{ route('front.product', $item['slug']) }}">
                            {{ Str::limit($item['name'], 40) }}

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
                            <span class="entry-meta d-inline">{{ $item['attribute']['names'][$optionkey] }}:</span>
                            <span class="entry-meta d-inline"><b>{{ $option_name }}</b></span>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    </section>

</aside>

