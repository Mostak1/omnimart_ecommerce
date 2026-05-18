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
                        <span
                            class="checkout_coupon_amount">{{ PriceHelper::setCurrencyPrice($discount ? $discount['discount'] : 0) }}</span>
                        <a href="{{ route('front.promo.destroy') }}"
                            class="btn btn-danger btn-sm ml-2 remove-checkout-coupon" title="{{ __('Remove coupon') }}">
                            <i class="icon-x"></i>
                        </a>
                    </td>
                </tr>

                @if (($shipping || PriceHelper::CheckDigital()) && PriceHelper::checkoutUsesDistrictShipping())
                    <tr class="{{ $shipping ? '' : 'd-none' }} set__shipping_price_tr">
                        <td>{{ __('Shipping') }}:</td>
                        <td class="text-gray-dark set__shipping_price">
                            {{ PriceHelper::setCurrencyPrice($shipping ? $shipping->price : 0) }}</td>
                    </tr>
                @endif
                @if (PriceHelper::CheckDigital() && PriceHelper::checkoutUsesStateShipping())
                    @php
                        $highest_state = DB::table('states')->whereStatus(1)->orderByDesc('price')->first();
                        $default_state_id = $highest_state ? $highest_state->id : (auth()->check() && auth()->user()->state_id ? auth()->user()->state_id : null);
                        $checkout_state_price = PriceHelper::StatePrce($default_state_id, $cart_total);
                    @endphp
                    <tr class="{{ $checkout_state_price > 0 ? '' : 'd-none' }} set__state_price_tr">
                        <td>{{ __('Shipping') }}:</td>
                        <td class="text-gray-dark set__state_price">{{ PriceHelper::setCurrencyPrice($checkout_state_price) }}</td>
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

    @if (PriceHelper::CheckDigital() == true && PriceHelper::checkoutUsesStateShipping() && DB::table('states')->whereStatus(1)->count() > 0)
        @if (data_get($site_visibility, 'checkout_order_summary', 1))
            <style>
            .state-options-container {
                display: flex;
                flex-direction: column;
                gap: 10px;
                margin-top: 10px;
            }

            .state-option-card {
                position: relative;
                border: 1.5px solid #e1e8ed;
                border-radius: 8px;
                padding: 12px 16px;
                background-color: #fff;
                cursor: pointer;
                transition: all 0.25s ease;
                display: block;
                margin-bottom: 0;
            }

            .state-option-card:hover {
                border-color: #cbd5e1;
                background-color: #f8fafc;
            }

            .state-option-card.active-state {
                border-color: #0d6efd;
                background-color: #f0f7ff;
                box-shadow: 0 4px 10px rgba(13, 110, 253, 0.06);
            }

            .state-option-card input[type="radio"] {
                position: absolute;
                opacity: 0;
                width: 0;
                height: 0;
            }

            .state-option-label {
                display: block;
                width: 100%;
                margin-bottom: 0;
                cursor: pointer;
            }

            .state-option-content {
                display: flex;
                align-items: center;
                justify-content: space-between;
                width: 100%;
            }

            .state-option-left {
                display: flex;
                align-items: center;
                gap: 12px;
            }

            .custom-checkbox-indicator {
                width: 18px;
                height: 18px;
                border: 2px solid #cbd5e1;
                border-radius: 4px;
                display: inline-flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
                background-color: #fff;
                flex-shrink: 0;
            }

            .state-option-card.active-state .custom-checkbox-indicator {
                border-color: #0d6efd;
                background-color: #0d6efd;
            }

            .custom-checkbox-indicator::after {
                content: '';
                width: 5px;
                height: 9px;
                border: solid white;
                border-width: 0 2px 2px 0;
                transform: rotate(45deg);
                opacity: 0;
                transition: all 0.1s ease;
                margin-bottom: 2px;
            }

            .state-option-card.active-state .custom-checkbox-indicator::after {
                opacity: 1;
            }

            .state-option-name {
                font-size: 14px;
                font-weight: 500;
                color: #334155;
            }

            .state-option-price {
                font-size: 13px;
                font-weight: 600;
                color: #475569;
                background-color: #f1f5f9;
                padding: 3px 8px;
                border-radius: 6px;
                transition: all 0.2s ease;
            }

            .state-option-card.active-state .state-option-price {
                color: #0288d1;
                background-color: #e0f2fe;
            }
            </style>

            <section class="card widget widget-featured-posts widget-order-summary p-4">
                <h3 class="widget-title">{{ __('Shipping Charge') }}</h3>
                <div class="row">
                    <div class="col-sm-12 mb-3">
                        <small
                            class="text-info">{{ __('Shipping is calculated from the selected admin State / Shipping Charge.') }}</small>
                        <p class="mb-0 mt-2 shipping_message d-none">
                            {{ __('Select shipping charge to calculate order total.') }}</p>
                    </div>
                    <div class="col-sm-12 mb-3">
                        @php
                            $states = DB::table('states')->whereStatus(1)->get();
                            $highest_state = $states->sortByDesc('price')->first();
                            $highest_state_id = $highest_state ? $highest_state->id : null;
                        @endphp
                        <div class="state-options-container">
                            @foreach ($states as $state)
                                @php
                                    $is_selected = ($state->id == $highest_state_id);
                                @endphp
                                <div class="state-option-card {{ $is_selected ? 'active-state' : '' }}" onclick="$(this).find('input').click();">
                                    <label class="state-option-label" for="state_sidebar_{{ $state->id }}" onclick="event.stopPropagation();">
                                        <input type="radio" name="state_id" id="state_sidebar_{{ $state->id }}" 
                                            value="{{ $state->id }}" class="state_id_select"
                                            data-href="{{ route('front.state.setup') }}"
                                            {{ $is_selected ? 'checked' : '' }}>
                                        <div class="state-option-content">
                                            <div class="state-option-left">
                                                <span class="custom-checkbox-indicator"></span>
                                                <span class="state-option-name">{{ $state->name }}</span>
                                            </div>
                                            <span class="state-option-price">
                                                @if ($state->type == 'fixed')
                                                    {{ PriceHelper::setCurrencyPrice($state->price) }}
                                                @else
                                                    {{ $state->price }}%
                                                @endif
                                            </span>
                                        </div>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                        @error('state_id')
                            <p class="text-danger state_message mt-2">{{ $message }}</p>
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
                        $defaultPaymentKeyword =
                            !PriceHelper::CheckDigitalPaymentGateway() && $gateways->contains('unique_keyword', 'cod')
                                ? 'cod'
                                : null;
                    @endphp
                    <select class="form-control payment_gateway" required>
                        <option value="" {{ $defaultPaymentKeyword ? '' : 'selected' }} disabled>
                            {{ __('Select a payment method') }}</option>
                        @foreach ($gateways as $gateway)
                            @if (PriceHelper::CheckDigitalPaymentGateway())
                                @if ($gateway->unique_keyword != 'cod')
                                    <option value="{{ $gateway->unique_keyword }}">{{ $gateway->name }}</option>
                                @endif
                            @else
                                <option value="{{ $gateway->unique_keyword }}"
                                    {{ $gateway->unique_keyword == $defaultPaymentKeyword ? 'selected' : '' }}>
                                    {{ $gateway->name }}</option>
                            @endif
                        @endforeach
                    </select>

                    @if ($setting->is_privacy_trams == 1)
                        <div class="form-group mt-4">
                            <div class="custom-control d-flex custom-checkbox">
                                <input class="custom-control-input me-2" type="checkbox" id="trams__condition_single"
                                    value="" checked>
                                <label class="custom-control-label flex-1" for="trams__condition_single">This site is
                                    protected by
                                    reCAPTCHA
                                    and the <a href="{{ $setting->policy_link }}" target="_blank">Privacy Policy</a>
                                    and <a href="{{ $setting->terms_link }}" target="_blank">Terms of Service</a>
                                    apply.</label>
                            </div>
                        </div>
                    @endif

                    <button id="single_checkout_payment" class="btn btn-primary mt-4 single_checkout_payment"
                        type="submit"><span>@lang('Order now')</span></button>
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
