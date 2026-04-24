<aside class="sidebar">
    <div class="padding-top-2x hidden-lg-up"></div>
    <!-- Items in Cart Widget-->

    @if (data_get($site_visibility, 'checkout_order_summary', 1))
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
    @endif

    @if (PriceHelper::CheckDigital() == true && DB::table('states')->whereStatus(1)->count() > 0)
    @if (data_get($site_visibility, 'checkout_order_summary', 1))
    <section class="card widget widget-featured-posts widget-order-summary p-4">
        <h3 class="widget-title">{{ __('Shipping Charge') }}</h3>
        <div class="row">
            <div class="col-sm-12 mb-3">
                <small class="text-info">{{ __('Shipping is calculated from district overrides or the default rate, plus extra charge after the first KG.') }}</small>
                <p class="mb-0 mt-2 shipping_message">{{ __('Select district to calculate shipping automatically.') }}</p>
            </div>
            <div class="col-sm-12 mb-3">
                <select name="state_id" class="form-control" id="state_id_select" required>
                    <option value="" selected disabled>{{ __('Select Shipping State') }}*</option>
                    @foreach (DB::table('states')->whereStatus(1)->get() as $state)
                        <option value="{{ $state->id }}" data-href="{{ route('front.state.setup') }}"
                            {{ Auth::check() && Auth::user()->state_id == $state->id ? 'selected' : '' }}>
                            {{ $state->name }}
                            @if ($state->type == 'fixed')
                                ({{ PriceHelper::setCurrencyPrice($state->price) }})
                            @else
                                ({{ $state->price }}%)
                            @endif

                        </option>
                    @endforeach
                </select>
                @error('state_id')
                    <p class="text-danger state_message">{{ $message }}</p>
                @enderror
            </div>
        </div>

    </section>
    @endif
    @endif

    @if (data_get($site_visibility, 'checkout_order_summary', 1))
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
    @endif



    <!-- Order Summary Widget-->
    @if (data_get($site_visibility, 'checkout_payment_methods', 1))
    <section class="card widget  widget-order-summary p-4 mb-0">
        <h3 class="widget-title">{{ __('Pay now') }}</h3>
        <div class="row">
            <div class="col-sm-12">
                @php
                    $gateways = DB::table('payment_settings')->whereStatus(1)->get();
                @endphp
                <select class="form-control payment_gateway" required>
                    <option value="" selected disabled>{{ __('Select a payment method') }}</option>
                    @foreach ($gateways as $gateway)
                        @if (PriceHelper::CheckDigitalPaymentGateway())
                            @if ($gateway->unique_keyword != 'cod')
                                <option value="{{ $gateway->unique_keyword }}">{{ $gateway->name }}</option>
                            @endif
                        @else
                            <option value="{{ $gateway->unique_keyword }}">{{ $gateway->name }}</option>
                        @endif
                    @endforeach
                </select>

                @if ($setting->is_privacy_trams == 1)
                    <div class="form-group mt-4">
                        <div class="custom-control d-flex custom-checkbox">
                            <input class="custom-control-input me-2" type="checkbox" id="trams__condition_single"
                                value="">
                            <label class="custom-control-label flex-1" for="trams__condition">This site is protected by
                                reCAPTCHA
                                and the <a href="{{ $setting->policy_link }}" target="_blank">Privacy Policy</a> and <a
                                    href="{{ $setting->terms_link }}" target="_blank">Terms of Service</a>
                                apply.</label>
                        </div>
                    </div>
                @endif

                <button id="single_checkout_payment" {{ $setting->is_privacy_trams == 1 ? 'disabled=true' : '' }}
                    class="btn btn-primary mt-4 single_checkout_payment" type="submit"><span>@lang('Pay now')</span></button>
            </div>

        </div>
    </section>
    @endif

</aside>

@section('script')
    <script>
        // Show the modal on #single_checkout_payment change
        $(document).on("click", "#single_checkout_payment", function() {
            let keyword = $('.payment_gateway').val();
            if (!keyword) {
                DangerNotification('{{ __('Please select a payment method') }}');
                return;
            }
            let modalElement = document.getElementById(keyword);

            if (modalElement) {
                // Open the modal using Bootstrap 5's API
                let modal = new bootstrap.Modal(modalElement);
                modal.show();

                let modalForm = $(modalElement).find('form').first();
                modalForm.find('.single-checkout-hidden').remove();

                // Copy checkout form inputs/selects/textareas into payment form
                $("#checkoutBilling").find('input, select, textarea').each(function() {
                    if (!$(this).attr('name')) {
                        return;
                    }

                    // Create a new hidden input field with the same name and value
                    let hiddenInput = $('<input>')
                        .attr('type', 'hidden') // Set the input type to hidden
                        .attr('name', $(this).attr('name')) // Use the same name attribute
                        .addClass('single-checkout-hidden')
                        .val($(this).val()); // Set the value of the hidden input

                    // Append the hidden input to the modal form
                    modalForm.append(hiddenInput);
                });
            }
        });

        // Handle the "Terms and Conditions" checkbox click
        $(document).on("click", "#trams__condition_single", function() {
            if ($("#trams__condition_single").is(':checked')) {
                console.log("check");
                // Enable the dropdown by assigning the ID and removing the disabled attribute
                $('.single_checkout_payment').attr('id', "single_checkout_payment");
                $('.single_checkout_payment').attr('disabled', false);
            } else {
                // Remove the ID and disable the dropdown when unchecked
                $('.single_checkout_payment').removeAttr('id');
                $('.single_checkout_payment').attr('disabled', true);
            }
        });
    </script>
@endsection
