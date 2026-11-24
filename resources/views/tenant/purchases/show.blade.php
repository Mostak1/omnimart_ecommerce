@extends('master.tenant')

@section('title', 'চালান ভাউচার #' . $purchase->invoice_no)

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-file-invoice text-primary mr-2"></i> পাইকারি ক্রয় চালান #{{ $purchase->invoice_no }}
        </h4>
        <p class="text-muted mb-0 font-14">সাপ্লায়ার চালান বিস্তারিত ও ইনভেন্টরি এন্ট্রি রেকর্ড</p>
    </div>
    <div>
        <button type="button" class="btn btn-primary font-weight-bold mr-2" onclick="window.print()">
            <i class="fas fa-print mr-1"></i> প্রিন্ট ভাউচার
        </button>
        <a href="{{ route('tenant.purchases.index') }}" class="btn btn-secondary font-weight-bold">
            <i class="fas fa-arrow-left mr-1"></i> তালিকায় ফিরুন
        </a>
    </div>
</div>

<div class="card shadow-sm p-4">
    <div class="row border-bottom pb-3 mb-4">
        <div class="col-sm-6">
            <h4 class="font-weight-bold text-dark mb-1">{{ tenant('shop_name') ?? 'OmniMart Store' }}</h4>
            <div class="text-muted small">
                {{ tenant('address') ?? 'দোকানের ঠিকানা' }}<br>
                মোবাইল: {{ tenant('phone') ?? '০১৭xxxxxxxx' }}
            </div>
        </div>
        <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
            <h5 class="text-primary font-weight-bold mb-1">ক্রয় চালান ভাউচার</h5>
            <div class="small"><b>চালান নং:</b> {{ $purchase->invoice_no }}</div>
            <div class="small"><b>তারিখ:</b> {{ $purchase->purchase_date ? $purchase->purchase_date->format('d/m/Y') : $purchase->created_at->format('d/m/Y') }}</div>
            <div class="small">
                <b>পেমেন্ট অবস্থা:</b> 
                @if($purchase->payment_status === 'paid')
                    <span class="badge badge-success">পরিশোধিত</span>
                @elseif($purchase->payment_status === 'partial')
                    <span class="badge badge-warning text-dark">আংশিক</span>
                @else
                    <span class="badge badge-danger">বকেয়া</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Supplier & Handler Info -->
    <div class="row mb-4">
        <div class="col-sm-6">
            <h6 class="font-weight-bold text-muted small text-uppercase">সাপ্লায়ার / মহাজন বিবরণী:</h6>
            <h5 class="font-weight-bold mb-1">{{ $purchase->supplier_name }}</h5>
            <div class="text-muted small">মোবাইল: {{ $purchase->supplier_phone ?? 'প্রদান করা হয়নি' }}</div>
        </div>
        <div class="col-sm-6 text-sm-right mt-3 mt-sm-0">
            <h6 class="font-weight-bold text-muted small text-uppercase">চালান গ্রহণকারী:</h6>
            <div class="font-weight-bold">{{ $purchase->creator ? $purchase->creator->name : 'এডমিন' }}</div>
            @if($purchase->notes)
                <div class="text-muted small mt-1">নোট: {{ $purchase->notes }}</div>
            @endif
        </div>
    </div>

    <!-- Items Table -->
    <div class="table-responsive mb-4">
        <table class="table table-bordered">
            <thead class="thead-light">
                <tr>
                    <th style="width: 50px;">#</th>
                    <th>পণ্যের নাম</th>
                    <th class="text-right" style="width: 140px;">ক্রয় দর (৳)</th>
                    <th class="text-center" style="width: 120px;">পরিমাণ</th>
                    <th class="text-right" style="width: 160px;">মোট মূল্য (৳)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($purchase->items as $idx => $item)
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td class="font-weight-bold">{{ $item->product_name }}</td>
                    <td class="text-right">৳{{ number_format((float)$item->unit_cost, 2) }}</td>
                    <td class="text-center">{{ (float)$item->quantity }}</td>
                    <td class="text-right font-weight-bold">৳{{ number_format((float)$item->total_cost, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="4" class="text-right font-weight-bold">সর্বমোট চালান মূল্য:</th>
                    <th class="text-right font-weight-bold text-primary" style="font-size: 16px;">
                        ৳{{ number_format((float)$purchase->total_amount, 2) }}
                    </th>
                </tr>
                <tr>
                    <th colspan="4" class="text-right font-weight-bold text-success">পরিশোধিত টাকা (Paid):</th>
                    <th class="text-right font-weight-bold text-success">
                        ৳{{ number_format((float)$purchase->paid_amount, 2) }}
                    </th>
                </tr>
                @if((float)$purchase->due_amount > 0)
                <tr>
                    <th colspan="4" class="text-right font-weight-bold text-danger">সাপ্লায়ারের বকেয়া (Due):</th>
                    <th class="text-right font-weight-bold text-danger">
                        ৳{{ number_format((float)$purchase->due_amount, 2) }}
                    </th>
                </tr>
                @endif
            </tfoot>
        </table>
    </div>
</div>
@endsection
