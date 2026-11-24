@extends('master.tenant')

@section('title', __('Mega Marketplace Commission & Due Statement'))

@section('content')

<div class="container-fluid">

    <!-- Page Header -->
    <div class="card mb-4 border-0 shadow-sm">
        <div class="card-body d-sm-flex align-items-center justify-content-between">
            <div>
                <h4 class="mb-1 font-weight-bold text-dark">
                    <i class="fas fa-hand-holding-usd text-warning mr-2"></i> {{ __('Marketplace Commission & Due Account') }}
                </h4>
                <p class="text-muted small mb-0">
                    {{ __('View sales commission charged by Mega Marketplace, monitor your dual due limits, and clear dues easily.') }}
                </p>
            </div>
            <div class="mt-3 mt-sm-0">
                <button type="button" class="btn btn-success font-weight-bold px-4" data-toggle="modal" data-target="#payDueModal">
                    <i class="fas fa-money-bill-wave mr-1"></i> {{ __('Pay Commission Due (bKash/Nagad)') }}
                </button>
            </div>
        </div>
    </div>

    @include('alerts.alerts')

    <!-- Status Alert if Overdue Limit -->
    @if($commission->isOverdueLimit())
        <div class="alert alert-danger shadow-sm border-0 d-flex align-items-center py-3 mb-4">
            <i class="fas fa-exclamation-triangle fa-2x mr-3"></i>
            <div>
                <h6 class="font-weight-bold mb-1">{{ __('Marketplace Sales Paused (Max Due Limit Exceeded)') }}</h6>
                <p class="mb-0 small">
                    আপনার বকেয়া কমিশন (৳{{ number_format($commission->current_due, 2) }}) সর্বোচ্চ ডিউ লিমিট (৳{{ number_format($commission->max_due_limit, 2) }}) অতিক্রম করেছে। ফলে সেন্ট্রাল মেগা মার্কেটপ্লেসে আপনার পণ্যগুলো সাময়িক পজ আছে। বকেয়া পরিশোধ করামাত্র স্বয়ংক্রিয়ভাবে মার্কেটপ্লেসে আপনার শপ লাইভ হয়ে যাবে। *(নোট: আপনার লোকাল শপের পিওএস কাউন্টার যথারীতি চালু আছে)*
                </p>
            </div>
        </div>
    @endif

    <!-- Financial KPI Cards -->
    <div class="row mb-4">
        <!-- Current Due -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-{{ $commission->isOverdueLimit() ? 'danger' : 'warning' }} shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-uppercase {{ $commission->isOverdueLimit() ? 'text-danger' : 'text-warning' }} mb-1">
                                {{ __('Current Due Commission') }}
                            </div>
                            <div class="h4 mb-0 font-weight-bold {{ $commission->isOverdueLimit() ? 'text-danger' : 'text-dark' }}">
                                ৳{{ number_format($commission->current_due, 2) }}
                            </div>
                            <small class="text-muted">রেট: {{ $commission->commission_rate }}% প্রতি সেলস</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-file-invoice-dollar fa-2x {{ $commission->isOverdueLimit() ? 'text-danger' : 'text-warning' }}"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Available Credit Before Limit -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Remaining Credit Limit') }}
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-success">
                                ৳{{ number_format($commission->availableCredit(), 2) }}
                            </div>
                            <small class="text-muted">সর্বোচ্চ ডিউ সীমা: ৳{{ number_format($commission->max_due_limit, 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-shield-alt fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Billing & Min Threshold -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Monthly Billing Day') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-dark">
                                প্রতি মাসের {{ $commission->billing_day }} তারিখ
                            </div>
                            <small class="text-muted">মিন লিমিট: ৳{{ number_format($commission->min_due_limit, 2) }} (নিচে হলে রোল-ওভার)</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Paid -->
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow-sm h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                {{ __('Total Paid Commission') }}
                            </div>
                            <div class="h4 mb-0 font-weight-bold text-dark">
                                ৳{{ number_format($commission->total_paid_commission, 2) }}
                            </div>
                            <small class="text-muted">সর্বমোট অর্জিত: ৳{{ number_format($commission->total_earned_commission, 2) }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-primary"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Ledger & Statement Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
            <h6 class="m-0 font-weight-bold text-primary">
                <i class="fas fa-list-alt mr-2"></i> {{ __('Commission Ledger & Payment History') }}
            </h6>
            <span class="badge badge-light border">সেন্ট্রাল অডিট ট্রেইল</span>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>{{ __('Date') }}</th>
                            <th>{{ __('Description / Note') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Due Balance (Before → After)') }}</th>
                            <th>{{ __('Payment / TrxID') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>{{ $log->id }}</td>
                                <td>
                                    <small class="text-muted">{{ $log->created_at->format('d M Y, h:i A') }}</small>
                                </td>
                                <td>
                                    <span>{{ $log->notes }}</span>
                                    @if($log->order_id)
                                        <br><span class="badge badge-light border"><i class="fas fa-shopping-bag"></i> Order #{{ $log->order_id }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($log->type === 'order_commission')
                                        <span class="badge badge-warning text-dark"><i class="fas fa-plus mr-1"></i> কমিশন চার্জ (+Due)</span>
                                    @elseif($log->type === 'commission_payment')
                                        <span class="badge badge-success"><i class="fas fa-check mr-1"></i> পরিশোধ (-Due)</span>
                                    @elseif($log->type === 'adjustment')
                                        <span class="badge badge-info"><i class="fas fa-sliders-h mr-1"></i> অ্যাডজাস্টমেন্ট</span>
                                    @else
                                        <span class="badge badge-secondary">{{ $log->type }}</span>
                                    @endif
                                </td>
                                <td>
                                    <b class="{{ $log->type === 'commission_payment' ? 'text-success' : 'text-danger' }}">
                                        {{ $log->type === 'commission_payment' ? '-' : '+' }}৳{{ number_format($log->amount, 2) }}
                                    </b>
                                </td>
                                <td>
                                    <small class="text-muted">
                                        ৳{{ number_format($log->balance_before, 2) }} → <b class="text-dark">৳{{ number_format($log->balance_after, 2) }}</b>
                                    </small>
                                </td>
                                <td>
                                    @if($log->payment_method)
                                        <span class="badge badge-light border text-uppercase font-weight-bold">{{ $log->payment_method }}</span>
                                    @endif
                                    @if($log->trx_id)
                                        <br><code>{{ $log->trx_id }}</code>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-5">
                                    <i class="fas fa-receipt fa-2x mb-2 text-muted"></i>
                                    <p class="mb-0">{{ __('No commission transactions yet.') }}</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>

</div>

<!-- Pay Due Modal -->
<div class="modal fade" id="payDueModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-lg">
            <form action="{{ route('tenant.commission.pay') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title font-weight-bold">
                        <i class="fas fa-money-bill-wave mr-2"></i> {{ __('Pay Commission Due') }}
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex justify-content-between align-items-center mb-4">
                        <div>
                            <span class="text-muted small">বর্তমান বকেয়া:</span>
                            <h4 class="font-weight-bold text-danger mb-0">৳{{ number_format($commission->current_due, 2) }}</h4>
                        </div>
                        <span class="badge badge-{{ $commission->isOverdueLimit() ? 'danger' : 'success' }} px-3 py-2">
                            {{ $commission->isOverdueLimit() ? 'লিমিট অতিক্রান্ত' : 'স্বাভাবিক' }}
                        </span>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">{{ __('Payment Amount (৳)') }} *</label>
                        <input type="number" step="0.01" min="1" max="100000" name="amount" class="form-control form-control-lg font-weight-bold"
                            value="{{ $commission->current_due > 0 ? $commission->current_due : '500' }}" required>
                        <small class="text-muted">{{ __('আপনি সম্পূর্ণ বা আংশিক বকেয়া পরিশোধ করতে পারবেন') }}</small>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">{{ __('Payment Method') }} *</label>
                        <select name="payment_method" class="form-control" required>
                            <option value="bkash">bKash (বিকাশ মার্চেন্ট / পার্সোনাল)</option>
                            <option value="nagad">Nagad (নগদ)</option>
                            <option value="rocket">Rocket (রকেট)</option>
                            <option value="bank">Bank Transfer</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label class="font-weight-bold text-dark">{{ __('Transaction ID (TrxID)') }} *</label>
                        <input type="text" name="trx_id" class="form-control" placeholder="e.g. 9AK38LQ71" required>
                        <small class="text-muted">{{ __('টাকা পাঠানোর পর প্রাপ্ত TrxID লিখুন') }}</small>
                    </div>

                    <div class="form-group mb-0">
                        <label class="font-weight-bold text-dark">{{ __('Additional Notes (Optional)') }}</label>
                        <input type="text" name="notes" class="form-control" placeholder="e.g. Cleared commission from 01711...">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Close') }}</button>
                    <button type="submit" class="btn btn-success px-4 font-weight-bold">
                        <i class="fas fa-check mr-1"></i> {{ __('Submit Payment') }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection
