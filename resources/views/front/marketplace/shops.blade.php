@extends('master.front')

@section('title', __('Explore Verified Shops') . ' | ' . $setting->title)

@section('content')

<!-- Page Title-->
<div class="page-title">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="breadcrumbs">
                    <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a></li>
                    <li class="separator"></li>
                    <li>{{ __('Explore Shops') }}</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Shops Directory Page Content -->
<div class="container padding-bottom-3x mb-1">
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0 p-4 bg-light rounded">
                <div class="row align-items-center">
                    <div class="col-md-7">
                        <h2 class="h3 font-weight-bold text-dark mb-1">🏪 {{ __('Mega Marketplace Shops Directory') }}</h2>
                        <p class="text-muted mb-0">{{ __('Browse verified partner stores, discover local products, and order directly.') }}</p>
                    </div>
                    <div class="col-md-5 mt-3 mt-md-0">
                        <form action="{{ route('front.shops.index') }}" method="GET" class="d-flex">
                            <input type="text" name="search" class="form-control mr-2" placeholder="{{ __('Search shop name or location...') }}" value="{{ request('search') }}">
                            <button type="submit" class="btn btn-primary px-4"><i class="icon-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shop Categories Filter -->
    @if($shopTypes->count() > 0)
        <div class="row mb-4">
            <div class="col-12">
                <div class="d-flex flex-wrap align-items-center">
                    <span class="font-weight-bold text-muted mr-3">{{ __('Categories:') }}</span>
                    <a href="{{ route('front.shops.index') }}" class="btn btn-sm mr-2 mb-2 {{ !request('type') ? 'btn-primary' : 'btn-outline-secondary' }}">
                        {{ __('All Shops') }}
                    </a>
                    @foreach($shopTypes as $type)
                        <a href="{{ route('front.shops.index', ['type' => $type, 'search' => request('search')]) }}"
                           class="btn btn-sm mr-2 mb-2 text-capitalize {{ request('type') == $type ? 'btn-primary' : 'btn-outline-secondary' }}">
                            {{ $type }}
                        </a>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    <!-- Shops Grid -->
    <div class="row">
        @forelse($shops as $shop)
            @php
                $comm = $shop->commission;
                $isPaused = $comm && $comm->marketplace_status === 'paused_due_limit';
                $domain = $shop->domains->first()?->domain ?? $shop->id;
                $centralDomain = config('tenancy.central_domains')[0] ?? 'localhost';
                $port = request()->getPort();
                $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
                $storefrontUrl = "http://{$domain}.{$centralDomain}{$portSuffix}";
            @endphp
            <div class="col-lg-4 col-md-6 mb-4">
                <div class="card h-100 shadow-sm border rounded-lg overflow-hidden position-relative hover-shadow transition">
                    <!-- Top banner header -->
                    <div class="p-4 bg-gradient-primary text-white text-center" style="background: linear-gradient(135deg, #2b3990 0%, #1e5799 100%); min-height: 110px;">
                        <span class="badge badge-light text-primary text-uppercase font-weight-bold px-2 py-1 mb-2">
                            {{ $shop->shop_type ?: 'General Store' }}
                        </span>
                        <h4 class="text-white font-weight-bold mb-0 text-truncate" title="{{ $shop->shop_name }}">
                            {{ $shop->shop_name }}
                        </h4>
                    </div>

                    <div class="card-body text-center pt-3 pb-3">
                        <div class="text-muted small mb-2">
                            <i class="icon-user text-primary mr-1"></i> <b>{{ __('Owner:') }}</b> {{ $shop->owner_name }}
                        </div>

                        @if($shop->address)
                            <div class="text-muted small mb-3 text-truncate" title="{{ $shop->address }}">
                                <i class="icon-map-pin text-danger mr-1"></i> {{ $shop->address }}
                            </div>
                        @endif

                        @if($isPaused)
                            <div class="alert alert-warning py-1 px-2 small mb-3">
                                <i class="icon-alert-triangle mr-1"></i> {{ __('Temporarily offline on Marketplace') }}
                            </div>
                        @else
                            <div class="badge badge-success px-3 py-1 mb-3">
                                <i class="icon-check-circle mr-1"></i> {{ __('Verified & Active') }}
                            </div>
                        @endif
                    </div>

                    <div class="card-footer bg-white border-top-0 pt-0 pb-3 px-3">
                        <div class="row">
                            <div class="col-6 pr-1">
                                <a href="{{ route('front.shops.show', $shop->id) }}" class="btn btn-outline-primary btn-sm btn-block">
                                    <i class="icon-shopping-bag mr-1"></i> {{ __('View Products') }}
                                </a>
                            </div>
                            <div class="col-6 pl-1">
                                <a href="{{ $storefrontUrl }}" target="_blank" class="btn btn-primary btn-sm btn-block">
                                    <i class="icon-external-link mr-1"></i> {{ __('Visit Store') }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center">
                <i class="icon-search text-muted display-4 mb-3"></i>
                <h4 class="text-muted">{{ __('No shops found matching your search criteria.') }}</h4>
                <a href="{{ route('front.shops.index') }}" class="btn btn-primary btn-sm mt-2">{{ __('View All Shops') }}</a>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    <div class="row mt-4">
        <div class="col-12 d-flex justify-content-center">
            {{ $shops->links() }}
        </div>
    </div>
</div>

@endsection
