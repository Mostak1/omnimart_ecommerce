@extends('master.tenant')

@section('title', 'দোকানের সেটিংস ও ইনভয়েস কনফিগারেশন')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-cog text-primary mr-2"></i> দোকানের সেটিংস ও ইনভয়েস কনফিগারেশন
        </h4>
        <p class="text-muted mb-0 font-14">দোকানের প্রোফাইল, ক্যাশ মেমো ফরম্যাট ও ভ্যাট রেট পরিবর্তন করুন</p>
    </div>
</div>

<form action="{{ route('tenant.settings.update') }}" method="POST">
    @csrf
    @method('PUT')
    <div class="row">
        <!-- Settings Form Column -->
        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <ul class="nav nav-pills nav-secondary" id="pills-tab" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link active font-weight-bold" id="pills-general-tab" data-toggle="pill" href="#pills-general" role="tab">
                                <i class="fas fa-store mr-2"></i> দোকানের সাধারণ তথ্য
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link font-weight-bold" id="pills-invoice-tab" data-toggle="pill" href="#pills-invoice" role="tab">
                                <i class="fas fa-file-invoice mr-2"></i> ক্যাশ মেমো ও বিলিং
                            </a>
                        </li>
                    </ul>
                </div>

                <div class="card-body">
                    <div class="tab-content mt-2 mb-3" id="pills-tabContent">
                        <!-- Tab 1: General Store Info -->
                        <div class="tab-pane fade show active" id="pills-general" role="tabpanel">
                            <div class="form-group px-0 mb-3">
                                <label class="font-weight-bold">দোকানের নাম <span class="text-danger">*</span></label>
                                <input type="text" name="shop_name" id="inputShopName" class="form-control" value="{{ $settings['shop_name'] ?? tenant('shop_name') }}" required>
                            </div>

                            <div class="form-group px-0 mb-3">
                                <label class="font-weight-bold">দোকানের স্লোগান / ট্যাগলাইন</label>
                                <input type="text" name="shop_slogan" id="inputShopSlogan" class="form-control" value="{{ $settings['shop_slogan'] ?? 'ন্যায্য মূল্যে সেরা পণ্যের বিশ্বস্ত প্রতিষ্ঠান' }}" placeholder="যেমন: আপনার আস্থার প্রতীক">
                            </div>

                            <div class="row">
                                <div class="col-md-6 form-group px-0 pr-md-2 mb-3">
                                    <label class="font-weight-bold">মোবাইল নম্বর</label>
                                    <input type="text" name="phone" id="inputPhone" class="form-control" value="{{ $settings['phone'] ?? tenant('phone') }}" placeholder="০১XXXXXXXXX">
                                </div>
                                <div class="col-md-6 form-group px-0 pl-md-2 mb-3">
                                    <label class="font-weight-bold">অফিসিয়াল ইমেইল</label>
                                    <input type="email" name="email" class="form-control" value="{{ $settings['email'] ?? '' }}" placeholder="store@example.com">
                                </div>
                            </div>

                            <div class="form-group px-0 mb-3">
                                <label class="font-weight-bold">দোকানের পূর্ণ ঠিকানা</label>
                                <textarea name="address" id="inputAddress" class="form-control" rows="2" placeholder="দোকান নং, মার্কেট/রোড, এলাকা, জেলা">{{ $settings['address'] ?? tenant('address') }}</textarea>
                            </div>
                        </div>

                        <!-- Tab 2: POS & Invoice Settings -->
                        <div class="tab-pane fade" id="pills-invoice" role="tabpanel">
                            <div class="row">
                                <div class="col-md-6 form-group px-0 pr-md-2 mb-3">
                                    <label class="font-weight-bold">ডিফল্ট ভ্যাট / ট্যাক্স হার (%)</label>
                                    <div class="input-group">
                                        <input type="number" step="0.01" name="vat_percentage" class="form-control" value="{{ $settings['vat_percentage'] ?? '0' }}" min="0" max="100">
                                        <div class="input-group-append"><span class="input-group-text">%</span></div>
                                    </div>
                                    <small class="text-muted">POS বিলে স্বয়ংক্রিয়ভাবে এই ভ্যাট যুক্ত হতে পারে (০ দিলে ভ্যাট ছাড়া)</small>
                                </div>
                                <div class="col-md-6 form-group px-0 pl-md-2 mb-3">
                                    <label class="font-weight-bold">মুদ্রার প্রতীক (Currency Symbol)</label>
                                    <input type="text" name="currency_symbol" class="form-control" value="{{ $settings['currency_symbol'] ?? '৳' }}">
                                </div>
                            </div>

                            <div class="form-group px-0 mb-3">
                                <label class="font-weight-bold">মেমোর নিচের ফুটার নোট / পলিসি</label>
                                <textarea name="invoice_footer_note" id="inputFooterNote" class="form-control" rows="3" placeholder="যেমন: বিক্রিত মাল ৭ দিনের মধ্যে পরিবর্তনের সুযোগ আছে। সাথে ক্যাশ মেমো আনবেন।">{{ $settings['invoice_footer_note'] ?? 'ধন্যবাদ, আবার আসবেন! সুস্থ থাকুন।' }}</textarea>
                                <small class="text-muted">এই মেসেজটি প্রতিটি থার্মাল রিসিপ্ট মেমোর শেষে প্রিন্ট হবে।</small>
                            </div>

                            <div class="custom-control custom-checkbox my-3">
                                <input type="checkbox" class="custom-control-input" id="pos_auto_print" name="pos_auto_print" value="1" {{ ($settings['pos_auto_print'] ?? '1') == '1' ? 'checked' : '' }}>
                                <label class="custom-control-label font-weight-bold" for="pos_auto_print">
                                    POS বিক্রয় সম্পন্ন হওয়ার সাথে সাথে স্বয়ংক্রিয়ভাবে প্রিন্ট ডায়লগ ওপেন হবে
                                </label>
                            </div>
                        </div>
                    </div>

                    <hr>
                    <button type="submit" class="btn btn-primary font-weight-bold px-4 py-2">
                        <i class="fas fa-save mr-2"></i> সেটিংস সংরক্ষণ করুন
                    </button>
                </div>
            </div>
        </div>

        <!-- Right: Live Preview of Receipt Header & Footer -->
        <div class="col-lg-4">
            <div class="card shadow-sm border">
                <div class="card-header bg-dark text-white font-weight-bold small text-uppercase">
                    <i class="fas fa-receipt mr-1"></i> ক্যাশ মেমো লাইভ প্রিভিউ (80mm)
                </div>
                <div class="card-body p-3 bg-light" style="font-family: monospace; font-size: 11px;">
                    <div class="border bg-white p-3 shadow-sm text-center">
                        <h5 class="font-weight-bold mb-1" id="previewShopName">{{ $settings['shop_name'] ?? tenant('shop_name') }}</h5>
                        <div class="text-muted small mb-1" id="previewShopSlogan">{{ $settings['shop_slogan'] ?? 'ন্যায্য মূল্যে সেরা পণ্যের প্রতিষ্ঠান' }}</div>
                        <div class="small" id="previewAddress">{{ $settings['address'] ?? tenant('address') ?? 'দোকানের ঠিকানা' }}</div>
                        <div class="small mb-2" id="previewPhone">মোবাইল: {{ $settings['phone'] ?? tenant('phone') ?? '০১XXXXXXXXX' }}</div>
                        
                        <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>
                        <div class="font-weight-bold">*** ক্যাশ মেমো ***</div>
                        <div class="d-flex justify-content-between small text-muted my-1">
                            <span>চালান: INV-SAMPLE</span>
                            <span>তারিখ: {{ date('d/m/Y') }}</span>
                        </div>
                        <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>

                        <div class="d-flex justify-content-between my-1">
                            <span>স্যাম্পল পণ্য ১ (২x)</span>
                            <span class="font-weight-bold">৳২০০.০০</span>
                        </div>

                        <div style="border-top: 1px solid #000; margin: 6px 0;"></div>
                        <div class="d-flex justify-content-between font-weight-bold">
                            <span>সর্বমোট বিল:</span>
                            <span>৳২০০.০০</span>
                        </div>
                        <div style="border-top: 1px dashed #000; margin: 6px 0;"></div>

                        <div class="mt-3 text-muted small" id="previewFooterNote">
                            {{ $settings['invoice_footer_note'] ?? 'ধন্যবাদ, আবার আসবেন!' }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
@endsection

@section('scripts')
<script>
    // Live update receipt preview
    $('#inputShopName').on('input', function() {
        $('#previewShopName').text($(this).val() || 'দোকানের নাম');
    });
    $('#inputShopSlogan').on('input', function() {
        $('#previewShopSlogan').text($(this).val());
    });
    $('#inputAddress').on('input', function() {
        $('#previewAddress').text($(this).val() || 'দোকানের ঠিকানা');
    });
    $('#inputPhone').on('input', function() {
        $('#previewPhone').text('মোবাইল: ' + ($(this).val() || '০১XXXXXXXXX'));
    });
    $('#inputFooterNote').on('input', function() {
        $('#previewFooterNote').text($(this).val() || 'ধন্যবাদ!');
    });
</script>
@endsection
