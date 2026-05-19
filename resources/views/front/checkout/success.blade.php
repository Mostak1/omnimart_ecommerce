@extends('master.front')

@section('title')
    {{ __('Order Success') }}
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
                    <li>{{ __('Success') }}</li>
                </ul>
            </div>
        </div>
    </div>
    @endif
    <!-- Page Content-->
    @if (data_get($site_visibility, 'checkout_success_content', 1))
    <div class="container padding-bottom-3x mb-1">
        <div class="card text-center">
            <div class="card-body">
                <h3 class="card-title text-success">{{ __('Thank you for your order') }}!</h3>
                <p class="card-text">{{ __('Your order has been placed and will be processed as soon as possible.') }}</p>
                <p class="card-text">{{ __('Make sure you make note of your order number, which is') }} <span
                        class="text-medium">{{ $order->transaction_number }}</span></p>
                <p class="card-text">{{ __('You will be receiving an email shortly with confirmation of your order.') }}

                </p>
                <div class="padding-top-1x padding-bottom-1x">

                    <a class="btn btn-primary m-4" href="{{ route('front.catalog') }}"><span><i
                                class="icon-package pr-2"></i> {{ __('View our products again') }}</span></a>

                </div>
            </div>
        </div>
    </div>
    @endif
@endsection

@section('script')
    @if (isset($fb_event_id))
        <script>
            if (typeof fbq === 'function') {
                fbq('track', 'Purchase', {
                    content_ids: [
                        @foreach ($cart as $key => $items)
                            '{{ PriceHelper::GetItemId($key) }}',
                        @endforeach
                    ],
                    content_type: 'product',
                    value: {{ (float)$order->grand_total }},
                    currency: '{{ PriceHelper::getCurrencyCode() }}',
                    num_items: {{ count($cart) }},
                    order_id: '{{ $order->transaction_number }}'
                }, {
                    eventID: '{{ $fb_event_id }}'
                });
            }

            // GTM purchase GA4 eCommerce
            window.dataLayer = window.dataLayer || [];
            window.dataLayer.push({
                'event': 'purchase',
                'ecommerce': {
                    'transaction_id': '{{ $order->transaction_number }}',
                    'value': {{ (float)$order->grand_total }},
                    'tax': {{ (float)$order->tax }},
                    'shipping': {{ (float)(json_decode($order->shipping, true)['price'] ?? 0) }},
                    'currency': '{{ PriceHelper::getCurrencyCode() }}',
                    'items': [
                        @foreach ($cart as $key => $items)
                            @php
                                $item = \App\Models\Item::find(\App\Helpers\PriceHelper::GetItemId($key));
                            @endphp
                            {
                                'item_id': '{{ \App\Helpers\PriceHelper::GetItemId($key) }}',
                                'item_name': '{{ $items['name'] }}',
                                'item_brand': '{{ $item && $item->brand ? $item->brand->name : '' }}',
                                'item_category': '{{ $item && $item->category ? $item->category->name : '' }}',
                                'price': {{ (float)$items['main_price'] }},
                                'quantity': {{ (int)$items['qty'] }}
                            }@if(!$loop->last),@endif
                        @endforeach
                    ]
                }
            });
        </script>
    @endif
@endsection
