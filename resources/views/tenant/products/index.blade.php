@extends('master.tenant')

@section('title', 'প্রোডাক্ট ও স্টক ব্যবস্থাপনা')

@section('styles')
<style>
    .catalog-item-card {
        transition: all 0.2s ease-in-out;
        border: 1px solid #e0e0e0;
        border-radius: 8px;
    }
    .catalog-item-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 6px 18px rgba(0,0,0,0.08);
        border-color: #1572e8;
    }
    .barcode-badge {
        font-family: monospace;
        letter-spacing: 0.5px;
        background: #f1f3f5;
        color: #212529;
        padding: 2px 6px;
        border-radius: 4px;
        font-size: 12px;
    }
    .product-thumb {
        width: 48px;
        height: 48px;
        object-fit: cover;
        border-radius: 6px;
        border: 1px solid #eee;
    }
    .stock-badge {
        font-size: 13px;
        font-weight: 700;
        padding: 5px 10px;
        border-radius: 20px;
    }
</style>
@endsection

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-boxes text-primary mr-2"></i> প্রোডাক্ট ও স্টক ব্যবস্থাপনা
        </h4>
        <p class="text-muted mb-0 font-14">দোকানের সমস্ত স্টক, বিক্রয় মূল্য, সেন্ট্রাল ক্যাটালগ ইমপোর্ট ও বারকোড লেবেল</p>
    </div>
    <div class="d-flex align-items-center">
        <!-- Search Global Catalog Modal Button -->
        <button type="button" class="btn btn-primary font-weight-bold mr-2 shadow-sm" data-toggle="modal" data-target="#globalCatalogModal" id="btnOpenGlobalCatalog">
            <i class="fas fa-cloud-download-alt mr-2"></i> Search Global Catalog
        </button>

        <!-- Add Custom Product Modal Button -->
        <button type="button" class="btn btn-success font-weight-bold shadow-sm" data-toggle="modal" data-target="#createCustomModal">
            <i class="fas fa-plus-circle mr-2"></i> লোকাল পণ্য যোগ করুন
        </button>
    </div>
</div>

<!-- Metric Overview Cards -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-cubes"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">মোট প্রোডাক্ট</p>
                            <h4 class="card-title">{{ number_format($totalProducts) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-info bubble-shadow-small">
                            <i class="fas fa-globe"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">সেন্ট্রাল ক্যাটালগ</p>
                            <h4 class="card-title">{{ number_format($importedProducts) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-success bubble-shadow-small">
                            <i class="fas fa-store"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">নিজস্ব লোকাল পণ্য</p>
                            <h4 class="card-title">{{ number_format($customProducts) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round {{ $lowStockCount > 0 ? 'border border-danger' : '' }}">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-danger bubble-shadow-small {{ $lowStockCount > 0 ? 'low-stock-pulse' : '' }}">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">স্টক অ্যালার্ট (কম স্টক)</p>
                            <h4 class="card-title text-danger font-weight-bold">{{ number_format($lowStockCount) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filters and Product List Table -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <form action="{{ route('tenant.products.index') }}" method="GET" class="row align-items-center">
            <div class="col-md-5 mb-2 mb-md-0">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="নাম বা বারকোড দিয়ে খুঁজুন..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> খুঁজুন</button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="type" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল ক্যাটাগরি / সোর্স</option>
                    <option value="imported" {{ request('type') == 'imported' ? 'selected' : '' }}>সেন্ট্রাল ক্যাটালগ (Imported)</option>
                    <option value="custom" {{ request('type') == 'custom' ? 'selected' : '' }}>নিজস্ব লোকাল পণ্য (Custom)</option>
                </select>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="filter" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল স্টক লেভেল</option>
                    <option value="low_stock" {{ request('filter') == 'low_stock' ? 'selected' : '' }}>⚠️ স্টক সতর্কতা (Low Stock)</option>
                    <option value="out_of_stock" {{ request('filter') == 'out_of_stock' ? 'selected' : '' }}>❌ স্টক শেষ (Out of Stock)</option>
                </select>
            </div>
            <div class="col-md-1 text-right">
                @if(request()->hasAny(['q', 'type', 'filter']))
                    <a href="{{ route('tenant.products.index') }}" class="btn btn-sm btn-light text-danger" title="ফিল্টার রিসেট"><i class="fas fa-times"></i> রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th style="width: 60px;">ছবি</th>
                        <th>পণ্যের নাম ও বিবরণ</th>
                        <th>বারকোড</th>
                        <th class="text-right">ক্রয় মূল্য</th>
                        <th class="text-right">বিক্রয় মূল্য</th>
                        <th class="text-center">বর্তমান স্টক</th>
                        <th class="text-center">সোর্স</th>
                        <th class="text-center" style="width: 170px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                    @php
                        $isLow = (float)$product->stock_quantity <= (float)$product->alert_quantity;
                        $isOut = (float)$product->stock_quantity <= 0;
                    @endphp
                    <tr class="{{ $isOut ? 'table-danger' : ($isLow ? 'table-warning' : '') }}">
                        <td>
                            @if($product->photo)
                                <img src="{{ url('/storage/images/' . $product->photo) }}" alt="{{ $product->name }}" class="product-thumb">
                            @else
                                <div class="product-thumb bg-light d-flex align-items-center justify-content-center text-muted">
                                    <i class="fas fa-box font-20"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $product->name }}</div>
                            <small class="text-muted">ইউনিট: {{ $product->unit ?? 'pcs' }} | অ্যালার্ট লিমিট: {{ $product->alert_quantity }}</small>
                        </td>
                        <td>
                            @if($product->barcode)
                                <span class="barcode-badge"><i class="fas fa-barcode mr-1"></i> {{ $product->barcode }}</span>
                            @else
                                <span class="text-muted font-italic font-12">বারকোড নেই</span>
                            @endif
                        </td>
                        <td class="text-right text-muted font-weight-bold">
                            ৳{{ number_format((float)$product->cost_price, 2) }}
                        </td>
                        <td class="text-right font-weight-bold text-primary" style="font-size: 15px;">
                            ৳{{ number_format((float)$product->retail_price, 2) }}
                        </td>
                        <td class="text-center">
                            @if($isOut)
                                <span class="badge badge-danger stock-badge">
                                    <i class="fas fa-times-circle mr-1"></i> শেষ (0)
                                </span>
                            @elseif($isLow)
                                <span class="badge badge-warning text-dark stock-badge">
                                    <i class="fas fa-exclamation-triangle mr-1"></i> {{ number_format((float)$product->stock_quantity) }} {{ $product->unit }} (কম)
                                </span>
                            @else
                                <span class="badge badge-success stock-badge">
                                    <i class="fas fa-check-circle mr-1"></i> {{ number_format((float)$product->stock_quantity) }} {{ $product->unit }}
                                </span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($product->is_custom)
                                <span class="badge badge-secondary"><i class="fas fa-store mr-1"></i> নিজস্ব</span>
                            @else
                                <span class="badge badge-info"><i class="fas fa-cloud mr-1"></i> সেন্ট্রাল ক্যাটালগ</span>
                            @endif
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <!-- Print Barcode Button -->
                                <a href="{{ route('tenant.products.print-barcode', $product->id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="বারকোড লেবেল প্রিন্ট">
                                    <i class="fas fa-barcode"></i>
                                </a>

                                <!-- Edit Modal Trigger -->
                                <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editModal-{{ $product->id }}" title="মূল্য ও স্টক এডিট">
                                    <i class="fas fa-edit"></i>
                                </button>

                                <!-- Delete Trigger -->
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmDelete({{ $product->id }}, '{{ addslashes($product->name) }}')" title="মুছে ফেলুন">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>

                            <form id="delete-form-{{ $product->id }}" action="{{ route('tenant.products.destroy', $product->id) }}" method="POST" style="display: none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>

                    <!-- Edit Modal for Product -->
                    <div class="modal fade" id="editModal-{{ $product->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered" role="document">
                            <div class="modal-content">
                                <form action="{{ route('tenant.products.update', $product->id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <div class="modal-header bg-primary text-white">
                                        <h5 class="modal-title font-weight-bold">
                                            <i class="fas fa-edit mr-2"></i> প্রোডাক্ট আপডেট: {{ Str::limit($product->name, 25) }}
                                        </h5>
                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                        </button>
                                    </div>
                                    <div class="modal-body">
                                        <div class="form-group">
                                            <label class="font-weight-bold">পণ্যের নাম</label>
                                            <input type="text" name="name" class="form-control" value="{{ $product->name }}" required>
                                        </div>
                                        <div class="form-group">
                                            <label class="font-weight-bold">বারকোড / SKU</label>
                                            <input type="text" name="barcode" class="form-control" value="{{ $product->barcode }}">
                                        </div>
                                        <div class="row">
                                            <div class="col-6 form-group">
                                                <label class="font-weight-bold text-success">বিক্রয় মূল্য (Retail Price)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                    <input type="number" step="0.01" name="retail_price" class="form-control font-weight-bold" value="{{ $product->retail_price }}" required>
                                                </div>
                                            </div>
                                            <div class="col-6 form-group">
                                                <label class="font-weight-bold text-muted">ক্রয় মূল্য (Cost Price)</label>
                                                <div class="input-group">
                                                    <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                                    <input type="number" step="0.01" name="cost_price" class="form-control" value="{{ $product->cost_price }}">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-4 form-group">
                                                <label class="font-weight-bold">বর্তমান স্টক</label>
                                                <input type="number" step="0.01" name="stock_quantity" class="form-control font-weight-bold" value="{{ $product->stock_quantity }}" required>
                                            </div>
                                            <div class="col-4 form-group">
                                                <label class="font-weight-bold text-danger">অ্যালার্ট স্টক</label>
                                                <input type="number" step="0.01" name="alert_quantity" class="form-control" value="{{ $product->alert_quantity }}">
                                            </div>
                                            <div class="col-4 form-group">
                                                <label class="font-weight-bold">ইউনিট</label>
                                                <input type="text" name="unit" class="form-control" value="{{ $product->unit ?? 'pcs' }}">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-light" data-dismiss="modal">বাতিল</button>
                                        <button type="submit" class="btn btn-primary font-weight-bold">সংরক্ষণ করুন</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="fas fa-box-open fa-3x mb-3 text-secondary d-block"></i>
                            <h5>কোনো প্রোডাক্ট পাওয়া যায়নি!</h5>
                            <p class="mb-3">উপরে <b>"Search Global Catalog"</b> বাটন চেপে সেন্ট্রাল ক্যাটালগ থেকে পণ্য আমদানি করুন অথবা নতুন নিজস্ব পণ্য যোগ করুন।</p>
                            <button type="button" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#globalCatalogModal">
                                <i class="fas fa-cloud-download-alt mr-1"></i> Search Global Catalog
                            </button>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($products->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            মোট {{ $products->total() }} টির মধ্যে {{ $products->firstItem() }} - {{ $products->lastItem() }} টি দেখানো হচ্ছে
        </div>
        <div>
            {{ $products->links() }}
        </div>
    </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- MODAL 1: Search Global Catalog Modal (One-click import into store stock)  -->
<!-- ========================================================================= -->
<div class="modal fade" id="globalCatalogModal" tabindex="-1" role="dialog" aria-labelledby="globalCatalogModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title font-weight-bold" id="globalCatalogModalLabel">
                    <i class="fas fa-search-plus mr-2"></i> Search Global Catalog (সেন্ট্রাল ক্যাটালগ থেকে পণ্য আমদানি)
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-4">
                <div class="bg-light p-3 rounded mb-4 border">
                    <div class="row align-items-center">
                        <div class="col-md-8 mb-2 mb-md-0">
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text bg-white"><i class="fas fa-barcode text-primary font-20"></i></span>
                                </div>
                                <input type="text" id="catalogSearchInput" class="form-control form-control-lg" placeholder="বারকোড স্ক্যান করুন অথবা পণ্যের নাম লিখুন..." autofocus>
                                <div class="input-group-append">
                                    <button class="btn btn-primary px-4" id="btnSearchCatalog" type="button">
                                        <i class="fas fa-search mr-1"></i> খুঁজুন
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4 text-muted small">
                            <i class="fas fa-info-circle text-info"></i> বারকোড স্ক্যানার দিয়ে সরাসরি স্ক্যান করলে বা টাইপ করে এন্টার চাপলে মুহূর্তেই ফলাফল আসবে।
                        </div>
                    </div>
                </div>

                <!-- Catalog Results Area -->
                <div id="catalogSearchResultsArea">
                    <div class="text-center py-5 text-muted" id="catalogSearchPlaceholder">
                        <i class="fas fa-barcode fa-4x text-secondary mb-3 d-block"></i>
                        <h5>বারকোড স্ক্যান করুন অথবা পণ্যের নাম দিয়ে সার্চ করুন</h5>
                        <p class="text-muted">সেন্ট্রাল ক্যাটালগে থাকা লক্ষাধিক পণ্যের মধ্য থেকে আপনার পছন্দের পণ্য সহজেই এক ক্লিকে দোকানে যুক্ত করুন।</p>
                    </div>

                    <div id="catalogSearchLoading" class="text-center py-5 d-none">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">খোঁজা হচ্ছে...</span>
                        </div>
                        <p class="mt-3 text-muted font-weight-bold">সেন্ট্রাল ক্যাটালগে খোঁজা হচ্ছে...</p>
                    </div>

                    <div class="row" id="catalogItemsGrid">
                        <!-- AJAX Items will be injected here -->
                    </div>
                </div>
            </div>

            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary font-weight-bold" data-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- MODAL 2: Create Custom Local Product Modal                                -->
<!-- ========================================================================= -->
<div class="modal fade" id="createCustomModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <form action="{{ route('tenant.products.custom') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-plus-circle mr-2"></i> নতুন নিজস্ব লোকাল পণ্য যোগ করুন
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">পণ্যের নাম <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="যেমন: তাজা মিষ্টি দই, খাঁটি গাভীর দুধ" required>
                    </div>
                    <div class="form-group px-0 mb-3">
                        <label class="font-weight-bold">বারকোড (ঐচ্ছিক - ফাঁকা রাখলে অটো জেনারেট হবে)</label>
                        <input type="text" name="barcode" class="form-control" placeholder="যেমন: LOC-10023">
                    </div>
                    <div class="row mb-3">
                        <div class="col-6">
                            <label class="font-weight-bold text-success">বিক্রয় মূল্য (Retail) <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" name="retail_price" class="form-control font-weight-bold" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-6">
                            <label class="font-weight-bold text-muted">ক্রয় মূল্য (Cost)</label>
                            <div class="input-group">
                                <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                <input type="number" step="0.01" name="cost_price" class="form-control" placeholder="0.00">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-4">
                            <label class="font-weight-bold">প্রারম্ভিক স্টক</label>
                            <input type="number" step="0.01" name="stock_quantity" class="form-control font-weight-bold" value="10">
                        </div>
                        <div class="col-4">
                            <label class="font-weight-bold text-danger">অ্যালার্ট লিমিট</label>
                            <input type="number" step="0.01" name="alert_quantity" class="form-control" value="5">
                        </div>
                        <div class="col-4">
                            <label class="font-weight-bold">পরিমাপের একক</label>
                            <input type="text" name="unit" class="form-control" value="pcs" placeholder="pcs/kg">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success font-weight-bold">
                        <i class="fas fa-check mr-1"></i> পণ্য তৈরি করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id, name) {
        if (confirm(`আপনি কি নিশ্চিত যে ‘${name}’ আপনার দোকান থেকে মুছে ফেলতে চান?`)) {
            document.getElementById(`delete-form-${id}`).submit();
        }
    }

    $(document).ready(function() {
        let debounceTimer;

        $('#catalogSearchInput').on('keyup', function(e) {
            if (e.keyCode === 13) {
                // Enter pressed
                clearTimeout(debounceTimer);
                performCatalogSearch();
            } else {
                clearTimeout(debounceTimer);
                debounceTimer = setTimeout(performCatalogSearch, 400);
            }
        });

        $('#btnSearchCatalog').on('click', function() {
            performCatalogSearch();
        });

        $('#btnOpenGlobalCatalog').on('click', function() {
            setTimeout(function() {
                $('#catalogSearchInput').focus();
            }, 500);
        });

        function performCatalogSearch() {
            let term = $('#catalogSearchInput').val().trim();
            if (term.length === 0) return;

            $('#catalogSearchPlaceholder').addClass('d-none');
            $('#catalogSearchLoading').removeClass('d-none');
            $('#catalogItemsGrid').empty();

            $.ajax({
                url: "{{ route('tenant.products.search-catalog') }}",
                type: 'GET',
                data: { q: term },
                success: function(res) {
                    $('#catalogSearchLoading').addClass('d-none');
                    if (res.success && res.items.length > 0) {
                        renderCatalogItems(res.items);
                    } else {
                        $('#catalogItemsGrid').html(`
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fas fa-search fa-3x mb-3 text-secondary d-block"></i>
                                <h5>কোনো পণ্য পাওয়া যায়নি!</h5>
                                <p>আপনার কাঙ্ক্ষিত পণ্যটি সেন্ট্রাল ক্যাটালগে না থাকলে আপনি সরাসরি <b>"লোকাল পণ্য যোগ করুন"</b> অপশন ব্যবহার করতে পারেন।</p>
                            </div>
                        `);
                    }
                },
                error: function(xhr) {
                    $('#catalogSearchLoading').addClass('d-none');
                    alert('সার্চ করতে ব্যর্থ হয়েছে: ' + (xhr.responseJSON?.message || 'Unknown error'));
                }
            });
        }

        function renderCatalogItems(items) {
            let html = '';
            items.forEach(function(item) {
                let photoUrl = item.photo ? `/storage/images/${item.photo}` : 'https://placehold.co/150x150?text=No+Photo';
                let centralPrice = item.discount_price > 0 ? item.discount_price : (item.previous_price || 0);
                let isImported = item.is_imported;
                let currentStock = isImported && item.tenant_product ? item.tenant_product.stock_quantity : 0;
                let localPrice = isImported && item.tenant_product ? item.tenant_product.retail_price : centralPrice;

                html += `
                <div class="col-md-6 mb-3">
                    <div class="card catalog-item-card h-100 p-3 mb-0">
                        <div class="row align-items-center">
                            <div class="col-3 text-center">
                                <img src="${photoUrl}" alt="${item.name}" class="img-fluid rounded border mb-2" style="max-height: 80px; object-fit: contain;">
                                <div class="badge badge-light border text-truncate w-100" style="font-size: 11px;">
                                    ${item.sku || 'No Barcode'}
                                </div>
                            </div>
                            <div class="col-9">
                                <h6 class="font-weight-bold text-dark mb-1 text-truncate" title="${item.name}">${item.name}</h6>
                                <div class="small text-muted mb-2">
                                    ক্যাটাগরি: <b>${item.category ? item.category.name : 'সাধারণ'}</b> | 
                                    সেন্ট্রাল মূল্য: <b>৳${parseFloat(centralPrice).toFixed(2)}</b>
                                </div>

                                ${isImported ? `
                                    <div class="alert alert-info py-1 px-2 mb-2 d-flex justify-content-between align-items-center" style="font-size: 12px;">
                                        <span><i class="fas fa-check-circle mr-1"></i> আপনার স্টকে আছে (মজুদ: <b>${currentStock}</b>)</span>
                                        <span>মূল্য: ৳${localPrice}</span>
                                    </div>
                                ` : ''}

                                <form class="form-inline import-item-form" data-item-id="${item.id}">
                                    <input type="hidden" name="item_id" value="${item.id}">
                                    <div class="input-group input-group-sm mr-2 mb-1" style="width: 120px;">
                                        <div class="input-group-prepend"><span class="input-group-text">৳</span></div>
                                        <input type="number" step="0.01" name="retail_price" class="form-control" value="${localPrice}" placeholder="বিক্রয় মূল্য" title="বিক্রয় মূল্য" required>
                                    </div>
                                    <div class="input-group input-group-sm mr-2 mb-1" style="width: 90px;">
                                        <input type="number" step="0.01" name="stock_quantity" class="form-control" value="10" placeholder="স্টক" title="স্টক সংখ্যা">
                                        <div class="input-group-append"><span class="input-group-text">pcs</span></div>
                                    </div>
                                    <button type="submit" class="btn btn-sm ${isImported ? 'btn-info' : 'btn-primary'} font-weight-bold mb-1">
                                        <i class="fas fa-plus mr-1"></i> ${isImported ? 'স্টক বাড়ান' : 'দোকানে অ্যাড'}
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                `;
            });

            $('#catalogItemsGrid').html(html);
        }

        // Handle one-click import AJAX form submit
        $(document).on('submit', '.import-item-form', function(e) {
            e.preventDefault();
            let $form = $(this);
            let $btn = $form.find('button[type="submit"]');
            let originalText = $btn.html();

            $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> যোগ হচ্ছে...');

            $.ajax({
                url: "{{ route('tenant.products.import-catalog') }}",
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: $form.serialize(),
                success: function(res) {
                    $btn.removeClass('btn-primary').addClass('btn-success').html('<i class="fas fa-check"></i> যোগ হয়েছে!');
                    setTimeout(function() {
                        $btn.prop('disabled', false).html(originalText);
                    }, 2000);
                },
                error: function(xhr) {
                    $btn.prop('disabled', false).html(originalText);
                    alert('ইমপোর্ট ব্যর্থ হয়েছে: ' + (xhr.responseJSON?.message || 'সমস্যা হয়েছে'));
                }
            });
        });
    });
</script>
@endsection

