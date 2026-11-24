@extends('master.tenant')

@section('title', 'বিক্রয় ও ক্যাশ মেমো ইতিহাস')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="page-title mb-1 font-weight-bold">
            <i class="fas fa-receipt text-primary mr-2"></i> বিক্রয় ও ক্যাশ মেমো ইতিহাস
        </h4>
        <p class="text-muted mb-0 font-14">দোকানের যাবতীয় বিক্রয় চালান, কাস্টমার হিসাব ও পেমেন্ট হিস্ট্রি</p>
    </div>
    <div>
        <a href="{{ route('tenant.pos.index') }}" class="btn btn-primary font-weight-bold shadow-sm">
            <i class="fas fa-cash-register mr-2"></i> নতুন ক্যাশ মেমো / POS
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
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">আজকের বিক্রয়</p>
                            <h4 class="card-title text-primary font-weight-bold">৳{{ number_format($todayTotal, 2) }}</h4>
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
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">সর্বমোট বিক্রয়</p>
                            <h4 class="card-title text-success font-weight-bold">৳{{ number_format($overallTotal, 2) }}</h4>
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
                            <i class="fas fa-hand-holding-usd"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">মোট বকেয়া (Due)</p>
                            <h4 class="card-title text-danger font-weight-bold">৳{{ number_format($totalDue, 2) }}</h4>
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
                            <i class="fas fa-file-invoice"></i>
                        </div>
                    </div>
                    <div class="col col-stats ml-3 ml-sm-0">
                        <div class="numbers">
                            <p class="card-category">মোট চালান সংখ্যা</p>
                            <h4 class="card-title font-weight-bold">{{ number_format($totalSalesCount) }} টি</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Sales Table and Filters -->
<div class="card shadow-sm">
    <div class="card-header bg-white border-bottom">
        <form action="{{ route('tenant.pos.history') }}" method="GET" class="row align-items-center">
            <div class="col-md-4 mb-2 mb-md-0">
                <div class="input-group">
                    <input type="text" name="q" class="form-control" placeholder="চালান নং বা কাস্টমার ফোন..." value="{{ request('q') }}">
                    <div class="input-group-append">
                        <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i></button>
                    </div>
                </div>
            </div>
            <div class="col-md-3 mb-2 mb-md-0">
                <select name="date" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল সময়</option>
                    <option value="today" {{ request('date') == 'today' ? 'selected' : '' }}>আজকের বিক্রয়</option>
                    <option value="this_month" {{ request('date') == 'this_month' ? 'selected' : '' }}>চলতি মাস</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <select name="payment_method" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল মাধ্যম</option>
                    <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>ক্যাশ (Cash)</option>
                    <option value="bkash" {{ request('payment_method') == 'bkash' ? 'selected' : '' }}>বিকাশ (bKash)</option>
                    <option value="nagad" {{ request('payment_method') == 'nagad' ? 'selected' : '' }}>নগদ (Nagad)</option>
                    <option value="due" {{ request('payment_method') == 'due' ? 'selected' : '' }}>বাকি (Due)</option>
                </select>
            </div>
            <div class="col-md-2 mb-2 mb-md-0">
                <select name="payment_status" class="form-control" onchange="this.form.submit()">
                    <option value="">সকল স্ট্যাটাস</option>
                    <option value="paid" {{ request('payment_status') == 'paid' ? 'selected' : '' }}>পরিশোধিত</option>
                    <option value="partial" {{ request('payment_status') == 'partial' ? 'selected' : '' }}>আংশিক</option>
                    <option value="due" {{ request('payment_status') == 'due' ? 'selected' : '' }}>বকেয়া</option>
                </select>
            </div>
            <div class="col-md-1 text-right">
                @if(request()->hasAny(['q', 'date', 'payment_method', 'payment_status']))
                    <a href="{{ route('tenant.pos.history') }}" class="btn btn-sm btn-light text-danger" title="রিসেট"><i class="fas fa-times"></i></a>
                @endif
            </div>
        </form>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="thead-light">
                    <tr>
                        <th>চালান নং (Invoice)</th>
                        <th>কাস্টমার</th>
                        <th>আইটেম</th>
                        <th class="text-right">মোট বিল</th>
                        <th class="text-right">জমা</th>
                        <th class="text-right">বকেয়া</th>
                        <th class="text-center">মাধ্যম</th>
                        <th class="text-center">পেমেন্ট অবস্থা</th>
                        <th>তারিখ ও সময়</th>
                        <th class="text-center" style="width: 100px;">মেমো প্রিন্ট</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <b class="text-primary">{{ $sale->invoice_no }}</b>
                        </td>
                        <td>
                            <div class="font-weight-bold">{{ $sale->customer_name }}</div>
                            <small class="text-muted">{{ $sale->customer_phone ?? 'ফোন নেই' }}</small>
                        </td>
                        <td>
                            <span class="badge badge-light border">{{ $sale->items->count() }} টি পণ্য</span>
                        </td>
                        <td class="text-right font-weight-bold">
                            ৳{{ number_format((float)$sale->total_amount, 2) }}
                        </td>
                        <td class="text-right text-success font-weight-bold">
                            ৳{{ number_format((float)$sale->paid_amount, 2) }}
                        </td>
                        <td class="text-right text-danger font-weight-bold">
                            ৳{{ number_format((float)$sale->due_amount, 2) }}
                        </td>
                        <td class="text-center">
                            <span class="badge badge-secondary text-uppercase">{{ $sale->payment_method }}</span>
                        </td>
                        <td class="text-center">
                            @if($sale->payment_status === 'paid')
                                <span class="badge badge-success">পরিশোধিত</span>
                            @elseif($sale->payment_status === 'partial')
                                <span class="badge badge-warning text-dark">আংশিক পরিশোধ</span>
                            @else
                                <span class="badge badge-danger">বকেয়া</span>
                            @endif
                        </td>
                        <td>
                            <div>{{ $sale->created_at->format('d/m/Y') }}</div>
                            <small class="text-muted">{{ $sale->created_at->format('h:i A') }}</small>
                        </td>
                        <td class="text-center">
                            <a href="{{ route('tenant.pos.invoice', $sale->id) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="ক্যাশ মেমো প্রিন্ট">
                                <i class="fas fa-print mr-1"></i> প্রিন্ট
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="fas fa-receipt fa-3x mb-2 text-secondary d-block"></i>
                            <h5>কোনো বিক্রয় রেকর্ড পাওয়া যায়নি!</h5>
                            <p>পিওএস টার্মিনালে যেয়ে পণ্য বিক্রয় করে ক্যাশ মেমো ইস্যু করুন।</p>
                            <a href="{{ route('tenant.pos.index') }}" class="btn btn-primary btn-sm">
                                <i class="fas fa-cash-register mr-1"></i> নতুন ক্যাশ মেমো তৈরি
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($sales->hasPages())
    <div class="card-footer bg-white d-flex justify-content-between align-items-center">
        <div class="text-muted small">
            মোট {{ $sales->total() }} টির মধ্যে {{ $sales->firstItem() }} - {{ $sales->lastItem() }} টি দেখানো হচ্ছে
        </div>
        <div>
            {{ $sales->links() }}
        </div>
    </div>
    @endif
</div>
@endsection
