@extends('master.back')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Marketplace Commission Audit & Ledger Logs') }}</b></h3>
                <a class="btn btn-primary btn-sm" href="{{ route('back.commissions.index') }}">
                    <i class="fas fa-arrow-left mr-1"></i> {{ __('Back to Accounts') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('back.commissions.logs') }}" method="GET" class="form-inline">
                <div class="form-group mr-3 mb-2">
                    <label class="mr-2 font-weight-bold">{{ __('Filter by Shop:') }}</label>
                    <select name="tenant_id" class="form-control form-control-sm select2">
                        <option value="all">{{ __('All Shops') }}</option>
                        @foreach($tenants as $t)
                            <option value="{{ $t->id }}" {{ request('tenant_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->shop_name }} ({{ $t->id }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group mr-3 mb-2">
                    <label class="mr-2 font-weight-bold">{{ __('Type:') }}</label>
                    <select name="type" class="form-control form-control-sm">
                        <option value="all">{{ __('All Types') }}</option>
                        <option value="order_commission" {{ request('type') == 'order_commission' ? 'selected' : '' }}>অর্ডার কমিশন (Order Commission)</option>
                        <option value="commission_payment" {{ request('type') == 'commission_payment' ? 'selected' : '' }}>বকেয়া পরিশোধ (Payment Received)</option>
                        <option value="adjustment" {{ request('type') == 'adjustment' ? 'selected' : '' }}>ম্যানুয়াল অ্যাডজাস্টমেন্ট (Adjustment)</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary btn-sm mb-2"><i class="fas fa-filter mr-1"></i> {{ __('Filter') }}</button>
                <a href="{{ route('back.commissions.logs') }}" class="btn btn-secondary btn-sm mb-2 ml-2">{{ __('Reset') }}</a>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-history mr-2"></i> {{ __('Transaction History Ledger') }}</h6>
        </div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-striped" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>#ID</th>
                            <th>{{ __('Date & Time') }}</th>
                            <th>{{ __('Shop') }}</th>
                            <th>{{ __('Type') }}</th>
                            <th>{{ __('Amount') }}</th>
                            <th>{{ __('Balance (Before → After)') }}</th>
                            <th>{{ __('Method / TrxID') }}</th>
                            <th>{{ __('Notes / Order') }}</th>
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
                                    <b>{{ $log->tenant?->shop_name ?? $log->tenant_id }}</b>
                                </td>
                                <td>
                                    @if($log->type === 'order_commission')
                                        <span class="badge badge-warning text-dark"><i class="fas fa-plus-circle mr-1"></i> বকেয়া চার্জ (+Due)</span>
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
                                        <span class="badge badge-light border text-uppercase">{{ $log->payment_method }}</span>
                                    @endif
                                    @if($log->trx_id)
                                        <br><code>{{ $log->trx_id }}</code>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $log->notes }}</small>
                                    @if($log->order_id)
                                        <br><a href="{{ route('back.order.invoice', $log->order_id) }}" target="_blank" class="badge badge-outline-primary">
                                            <i class="fas fa-file-invoice"></i> Order #{{ $log->order_id }}
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    {{ __('No transaction logs found.') }}
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

@endsection
