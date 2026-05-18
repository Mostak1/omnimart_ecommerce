@extends('master.front')
@section('title')
    {{ __('Payment') }}
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
                    <li>{{ __('Review your order and pay') }}</li>
                </ul>
            </div>
        </div>
    </div>
    @endif
    <!-- Page Content-->
    <div class="container padding-bottom-3x mb-1 checkut-page">
        <div class="row">
            <!-- Payment Methode-->
            @if (data_get($site_visibility, 'checkout_billing_form', 1) || data_get($site_visibility, 'checkout_payment_methods', 1))
            <div class="col-xl-9 col-lg-8">
                <div class="steps flex-sm-nowrap mb-5"> <a class="step" href="{{ route('front.checkout.billing') }}">
                        <h4 class="step-title"><i class="icon-check-circle"></i>1. {{ __('Invoice to') }}:</h4>
                    </a> <a class="step" href="{{ route('front.checkout.shipping') }}">
                        <h4 class="step-title"><i class="icon-check-circle"></i>2. {{ __('Ship to') }}:</h4>
                    </a> <a class="step active" href="{{ route('front.checkout.payment') }}">
                        <h4 class="step-title">3. {{ __('Review and pay') }}</h4>
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6 class="pb-2 widget-title2">{{ __('Review Your Order') }} :</h6>
                        
                        <div class="row">
                            <div class="col-sm-6 mb-4">
                                <h6 class="fz-16-bold">{{ __('Invoice address') }} :</h6>
                                @php

                                    $ship = Session::get('shipping_address');
                                    $bill = Session::get('billing_address');
                                    $selectedDistrict = $ship['ship_country'] ?? ($bill['bill_country'] ?? '');
                                @endphp
                                <input type="hidden" id="selected_checkout_district" value="{{ $selectedDistrict }}">
                                <ul class="list-unstyled">
                                    <li><span class="text-muted pay-label">{{ __('Name') }}:
                                        </span>{{ $ship['ship_first_name'] }}</li>
                                    @if (PriceHelper::CheckDigital())
                                        <li><span class="text-muted pay-label">{{ __('Address') }}:
                                            </span>{{ $ship['ship_address1'] }} {{ @$ship['ship_address2'] }}</li>
                                    @endif
                                    <li><span class="text-muted pay-label">{{ __('Phone') }}: </span>{{ $ship['ship_phone'] }}
                                    </li>
                                </ul>
                            </div>
                            <div class="col-sm-6  mb-4">
                                <h6 class="fz-16-bold">{{ __('Shipping address') }} :</h6>
                                <ul class="list-unstyled">
                                    <li><span class="text-muted pay-label">{{ __('Name') }}:
                                        </span>{{ $bill['bill_first_name'] }}</li>
                                    @if (PriceHelper::CheckDigital())
                                        <li><span class="text-muted pay-label">{{ __('Address') }}:
                                            </span>{{ $ship['ship_address1'] }} {{ @$ship['ship_address2'] }}</li>
                                    @endif
                                    <li><span class="text-muted pay-label">{{ __('Phone') }}: </span>{{ $bill['bill_phone'] }}
                                    </li>
                                </ul>

                              
                               
                            </div>
                        </div>
                                @if (PriceHelper::CheckDigital() == true && PriceHelper::checkoutUsesDistrictShipping())
                                <h6 class="pb-2 widget-title2">{{ __('Shipping Charge') }} :</h6>
                                @endif
                                <div class="row">
                                @if (PriceHelper::CheckDigital() == true && PriceHelper::checkoutUsesDistrictShipping())
                                    <div class="col-sm-6  mb-4">
                                    <div class="border rounded p-3">
                                        <p class="mb-2"><strong>{{ __('District') }}:</strong>
                                            {{ $shipping && $shipping->calculated_district ? $shipping->calculated_district : __('Not Selected') }}</p>
                                        <p class="mb-2"><strong>{{ __('Total Weight') }}:</strong>
                                            {{ $shipping ? number_format($shipping->calculated_weight, 2) : '0.00' }} KG</p>
                                        <p class="mb-0"><strong>{{ __('Shipping Charge') }}:</strong>
                                            <span class="set__shipping_price">{{ PriceHelper::setCurrencyPrice($shipping ? $shipping->price : 0) }}</span>
                                        </p>
                                    </div>
                                    @error('shipping_id')
                                        <p class="text-danger mt-2 shipping_message">{{ $message }}</p>
                                    @enderror
                            </div>
                                @endif
                            @if (PriceHelper::CheckDigital() == true && PriceHelper::checkoutUsesStateShipping() && DB::table('states')->whereStatus(1)->count() > 0)
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
                            <div class="col-sm-12  mb-4">
                                    <h6 class="pb-1 widget-title2">{{ __('Shipping Charge') }} :</h6>
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
                                                <label class="state-option-label" for="state_payment_{{ $state->id }}" onclick="event.stopPropagation();">
                                                    <input type="radio" name="state_id" id="state_payment_{{ $state->id }}" 
                                                        value="{{ $state->id }}" class="state_id_select state_id_setup"
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
                                    <small class="text-primary state_message d-none">{{ __('Please select shipping state') }}</small>
                                    @error('state_id')
                                        <p class="text-danger state_message mt-2">{{ $message }}</p>
                                    @enderror
                            </div>
                            @endif
                        </div>
                        @if (data_get($site_visibility, 'checkout_payment_methods', 1))
                        <h6 class="pb-2 widget-title2">{{ __('Pay With') }} :</h6>
                        <div class="row mt-4">
                            <div class="col-12">
                                <div class="payment-methods">
                                    @php
                                        $gateways = DB::table('payment_settings')->whereStatus(1)->get();
                                    @endphp
                                    @foreach ($gateways as $gateway)
                                        @if (PriceHelper::CheckDigitalPaymentGateway())
                                            @if ($gateway->unique_keyword != 'cod')
                                                <div class="single-payment-method">
                                                    <a class="text-decoration-none " href="#" data-bs-toggle="modal"
                                                        data-bs-target="#{{ $gateway->unique_keyword }}">
                                                        <img class=""
                                                            src="{{ url('/storage/images/' . $gateway->photo) }}"
                                                            alt="{{ $gateway->name }}" title="{{ $gateway->name }}">
                                                        <p>{{ $gateway->name }}</p>
                                                    </a>
                                                </div>
                                            @endif
                                        @else
                                            <div class="single-payment-method">
                                                <a class="text-decoration-none" href="#" data-bs-toggle="modal"
                                                    data-bs-target="#{{ $gateway->unique_keyword }}">
                                                    <img class=""
                                                        src="{{ url('/storage/images/' . $gateway->photo) }}"
                                                        alt="{{ $gateway->name }}" title="{{ $gateway->name }}">
                                                    <p>{{ $gateway->name }}</p>
                                                </a>
                                            </div>
                                        @endif
                                    @endforeach

                                </div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @if (data_get($site_visibility, 'checkout_payment_methods', 1))
                @include('includes.checkout_modal')
                @endif

            </div>
            @endif
            <!-- Sidebar  -->
            @if (data_get($site_visibility, 'checkout_order_summary', 1))
            <div class="col-xl-3 col-lg-4">
                @include('includes.checkout_sitebar',$cart)
            </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
    @if (isset($fb_event_id))
        <script>
            if (typeof fbq === 'function') {
                fbq('track', 'InitiateCheckout', {
                    content_ids: [
                        @foreach ($cart as $key => $items)
                            '{{ PriceHelper::GetItemId($key) }}',
                        @endforeach
                    ],
                    content_type: 'product',
                    value: {{ (float)$grand_total }},
                    currency: '{{ PriceHelper::getCurrencyCode() }}',
                    num_items: {{ count($cart) }}
                }, {
                    eventID: '{{ $fb_event_id }}'
                });
            }
        </script>
    @endif
@endsection
