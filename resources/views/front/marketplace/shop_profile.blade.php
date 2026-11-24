@extends('master.front')

@section('title', $shop->shop_name . ' | ' . __('Mega Marketplace'))

@section('content')

<!-- Page Title-->
<div class="page-title">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="breadcrumbs">
                    <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                    <li class="separator"></li>
                    <li><a href="{{ route('front.shops.index') }}">{{ __('Shops') }}</a></li>
                    <li class="separator"></li>
                    <li>{{ $shop->shop_name }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<div class="container padding-bottom-3x mb-1">
    <!-- Shop Header Profile Card -->
    <div class="card shadow-sm border-0 mb-4 overflow-hidden rounded-lg">
        <div class="p-4 text-white" style="background: linear-gradient(135deg, #1b2838 0%, #2a5298 100%);">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <span class="badge badge-warning text-dark text-uppercase font-weight-bold mb-2">
                        {{ $shop->shop_type ?: 'Retailer' }}
                    </span>
                    <h2 class="text-white font-weight-bold mb-1">{{ $shop->shop_name }}</h2>
                    <p class="text-white-50 mb-2">
                        <i class="icon-user mr-1"></i> <b>{{ __('Proprietor:') }}</b> {{ $shop->owner_name }} &nbsp;|&nbsp;
                        <i class="icon-map-pin mr-1"></i> {{ $shop->address ?: __('Bangladesh') }}
                    </p>
                    @if($shop->commission && $shop->commission->marketplace_status === 'paused_due_limit')
                        <span class="badge badge-danger px-3 py-1">
                            <i class="icon-alert-triangle mr-1"></i> {{ __('Shop Offline on Marketplace (Pending Commission Settlement)') }}
                        </span>
                    @else
                        <span class="badge badge-success px-3 py-1">
                            <i class="icon-check-circle mr-1"></i> {{ __('Verified Marketplace Partner') }}
                        </span>
                    @endif
                </div>

                <div class="col-md-4 text-md-right mt-3 mt-md-0">
                    @php
                        $domain = $shop->domains->first()?->domain ?? $shop->id;
                        $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
                        $port = request()->getPort();
                        $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
                        $storefrontUrl = "http://{$domain}.{$centralDomain}{$portSuffix}";
                    @endphp
                    <a href="{{ $storefrontUrl }}" target="_blank" class="btn btn-light font-weight-bold px-4 py-2 shadow-sm">
                        <i class="icon-external-link mr-1"></i> {{ __('Visit Independent Store') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Products Header -->
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <h4 class="mb-0 font-weight-bold text-dark">
            <i class="icon-package mr-2 text-primary"></i> {{ __('Products Available From This Shop') }} ({{ $items->total() }})
        </h4>
        <a href="{{ route('front.shops.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="icon-arrow-left mr-1"></i> {{ __('Back to All Shops') }}
        </a>
    </div>

    <!-- Products Grid -->
    <div class="row">
        @forelse($items as $item)
            <div class="col-lg-3 col-md-4 col-6 mb-4">
                <div class="product-card card h-100 shadow-sm border rounded">
                    @if ($item->is_stock())
                        <div class="product-badge bg-success">{{ __('In Stock') }}</div>
                    @else
                        <div class="product-badge bg-secondary border-default text-body">{{ __('Out of stock') }}</div>
                    @endif

                    <div class="product-thumb p-3 text-center">
                        <img src="{{ asset('assets/images/' . $item->photo) }}" alt="{{ $item->name }}" class="img-fluid" style="max-height: 180px; object-fit: contain;">
                    </div>

                    <div class="product-card-body p-3 text-center">
                        <div class="product-category text-muted small text-truncate">
                            <span class="badge badge-light border text-primary">
                                <i class="icon-store"></i> {{ $shop->shop_name }}
                            </span>
                        </div>
                        <h3 class="product-title h6 mt-2 mb-2 text-truncate" title="{{ $item->name }}">
                            <a href="{{ route('front.product', $item->slug) }}" class="text-dark">{{ $item->name }}</a>
                        </h3>
                        <h4 class="product-price font-weight-bold text-primary mb-3">
                            {{ PriceHelper::grandCurrency() }}{{ PriceHelper::grandPrice($item) }}
                        </h4>
                        <a class="btn btn-outline-primary btn-sm btn-block" href="{{ route('front.product', $item->slug) }}">
                            <i class="icon-eye mr-1"></i> {{ __('View Details') }}
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <i class="icon-package text-muted display-4 mb-3"></i>
                <h5 class="text-muted">{{ __('This shop has not uploaded central catalog products yet.') }}</h5>
                <p class="text-muted small">{{ __('You can visit the shop\'s independent local storefront to browse their direct inventory.') }}</p>
                <a href="{{ $storefrontUrl }}" target="_blank" class="btn btn-primary btn-sm px-4 mt-2">
                    <i class="icon-external-link mr-1"></i> {{ __('Visit Storefront Directly') }}
                </a>
            </div>
        @endforelse
    </div>

    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $items->links() }}
        </div>
    </div>
</div>

@endsection
