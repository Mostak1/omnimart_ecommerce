@extends('master.tenant')

@section('title', 'পাইকারি ক্রয় ও সাপ্লায়ার চালান')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-truck-loading text-primary mr-2"></i> পাইকারি ক্রয় ও সাপ্লায়ার চালান (Purchases)
        </h4>
        <p class="text-muted mb-0 font-14">ভেন্ডর/মহাজনের থেকে মাল কেনার চালান, স্টক ইন-ফ্লো ও বাকি হিসাব</p>
    </div>
    <div>
        <a href="{{ route('tenant.purchases.create') }}" class="btn btn-success font-weight-bold shadow-sm">
            <i class="fas fa-plus-circle mr-2"></i> নতুন ক্রয় চালান এন্ট্রি
        </a>
    </div>
</div>

<!-- Metrics Overview -->
<div class="row mb-4">
    <div class="col-sm-6 col-md-3">
        <div class="card card-stats card-round">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-icon">
                        <div class="icon-big text-center icon-primary bubble-shadow-small">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">মোট ক্রয় মূল্য</p>
                            <h4 class="card-title text-primary font-weight-bold">৳{{ number_format($totalPurchasesAmount, 2) }}</h4>
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
                            <i class="fas fa-check-double"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">পরিশোধিত টাকা</p>
                            <h4 class="card-title text-success font-weight-bold">৳{{ number_format($totalPaidAmount, 2) }}</h4>
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
                        <div class="icon-big text-center icon-danger bubble-shadow-small">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">সাপ্লায়ার বকেয়া (Due)</p>
                            <h4 class="card-title text-danger font-weight-bold">৳{{ number_format($totalDueAmount, 2) }}</h4>
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
                            <i class="fas fa-boxes"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">মোট চালান সংখ্যা</p>
                            <h4 class="card-title font-weight-bold">{{ number_format($totalPurchasesCount) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Table & Filter -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <form action="{{ route('tenant.purchases.index') }}" method="GET" class="row align-items-center">
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="চালান নং বা সাপ্লায়ারের নাম/ফোন..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> খুঁজুন</button>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-2 mb-md-0">
                <select name="payment_status" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল পেমেন্ট স্ট্যাটাস</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>পরিশোধিত (Paid)</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>আংশিক পরিশোধ (Partial)</option>
                    <option value="due" {{ request('payment_status') == 'due' ? 'selected' : '' }}>বকেয়া (Due)</option>
                </select>
            </div>
            <div class="col-md-2 text-right">
                @if(request()->hasAny(['q', 'payment_status']))
                    <a href="{{ route('tenant.purchases.index') }}" class="btn btn-sm btn-light text-danger" title="রিসেট"><i class="fas fa-times"></i> রিসেট</a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>চালান নং</th>
                        <th>সাপ্লায়ার / মহাজন</th>
                        <th>পণ্য সংখ্যা</th>
                        <th class="text-right">মোট বিল</th>
                        <th class="text-right">পরিশোধ</th>
                        <th class="text-right">বকেয়া</th>
                        <th class="text-center">স্ট্যাটাস</th>
                        <th>চালানের তারিখ</th>
                        <th class="text-center" style="width: 130px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($purchases as $p)
                    <tr>
                        <td>
                            <b class="text-primary">{{ $p->invoice_no }}</b>
                        </td>
                        <td>
                            <div class="font-weight-bold text-dark">{{ $p->supplier_name }}</div>
                            <small class="text-muted">{{ $p->supplier_phone ?? 'ফোন নেই' }}</small>
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $p->items->count() }} টি আইটেম</span>
                        </td>
                        <td class="text-right font-weight-bold">
                            ৳{{ number_format((float)$p->total_amount, 2) }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            ৳{{ number_format((float)$p->paid_amount, 2) }}
                        </td>
                        <td class="text-right text-danger font-weight-bold">
                            ৳{{ number_format((float)$p->due_amount, 2) }}
                        </td>
                        <td class="text-center">
                            @if($p->payment_status === 'paid')
                                <span class="badge badge-success">পরিশোধিত</span>
                            @elseif($p->payment_status === 'partial')
                                <span class="badge badge-warning text-dark">আংশিক</span>
                            @else
                                <span class="badge badge-danger">বকেয়া</span>
                            @endif
                        </td>
                        <td>
                            {{ $p->purchase_date ? $p->purchase_date->format('d/m/Y') : $p->created_at->format('d/m/Y') }}
                        </td>
                        <td class="text-center">
                            <div class="btn-group">
                                <a href="{{ route('tenant.purchases.show', $p->id) }}" class="btn btn-sm btn-outline-info" title="চালান ভাউচার দেখুন">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-danger" onclick="confirmCancel({{ $p->id }}, '{{ $p->invoice_no }}')" title="চালান বাতিল করুন">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </div>
                            <form id="delete-purchase-{{ $p->id }}" action="{{ route('tenant.purchases.destroy', $p->id) }}" method="POST" style="display:none;">
                                @csrf
                                @method('DELETE')
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="fas fa-truck-loading fa-3x mb-2 text-secondary d-block"></i>
                            <h5>কোনো পাইকারি ক্রয়ের চালান পাওয়া যায়নি!</h5>
                            <p>মহাজন বা পাইকারি বিক্রেতার থেকে মাল ক্রয়ের পর চালান এন্ট্রি করে স্টক বৃদ্ধি করুন।</p>
                            <a href="{{ route('tenant.purchases.create') }}" class="btn btn-success btn-sm">
                                <i class="fas fa-plus mr-1"></i> নতুন চালান এন্ট্রি
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($purchases->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            মোট {{ $purchases->total() }} টির মধ্যে {{ $purchases->firstItem() }} - {{ $purchases->lastItem() }} টি দেখানো হচ্ছে
        </div>
        <div>
            {{ $purchases->links() }}
        </div>
    </div>
    @endif
</div>
@endsection

@section('scripts')
<script>
    function confirmCancel(id, inv) {
        if (confirm(`আপনি কি নিশ্চিত যে চালান #${inv} বাতিল করতে চান? এর সাথে যুক্ত পণ্যের স্টকও স্বয়ংক্রিয়ভাবে মাইনাস হয়ে যাবে।`)) {
            document.getElementById(`delete-purchase-${id}`).submit();
        }
    }
</script>
@endsection
