@extends('master.back')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Marketplace Commission & Shop Due Management') }}</b></h3>
                <div>
                    <a class="btn btn-info btn-sm mr-2" href="{{ route('back.commissions.logs') }}">
                        <i class="fas fa-list-alt mr-1"></i> {{ __('View Full Audit Logs') }}
                    </a>
                    <a class="btn btn-primary btn-sm" href="{{ route('back.tenants.index') }}">
                        <i class="fas fa-store mr-1"></i> {{ __('Manage Shops') }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI Metric Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                {{ __('Total Commission Earned') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($metrics['total_earned'], 2) }}
                            </div>
                            <small class="text-muted">{{ __('Platform lifetime earnings') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-coins fa-2x text-success"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                {{ __('Total Pending Due') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-danger">
                                ৳{{ number_format($metrics['total_due'], 2) }}
                            </div>
                            <small class="text-muted">{{ __('Outstanding shop dues') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-hand-holding-usd fa-2x text-danger"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                {{ __('Total Commission Collected') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                ৳{{ number_format($metrics['total_paid'], 2) }}
                            </div>
                            <small class="text-muted">{{ __('Received from shops') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                {{ __('Paused Due to Limit') }}
                            </div>
                            <div class="h5 mb-0 font-weight-bold text-warning">
                                {{ $metrics['paused_count'] }} / {{ $metrics['total_shops'] }}
                            </div>
                            <small class="text-muted">{{ __('Shops over max due limit') }}</small>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-pause-circle fa-2x text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Shops Commission Table -->
    <div class="card shadow mb-4">
        <div class="card-header py-3 bg-white">
            <h6 class="m-0 font-weight-bold text-primary"><i class="fas fa-store mr-2"></i> {{ __('Shop Commission Accounts & Due Limits') }}</h6>
        </div>
        <div class="card-body">
            @include('alerts.alerts')

            <div class="table-responsive">
                <table class="table table-bordered table-striped" id="admin-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('Shop Details') }}</th>
                            <th>{{ __('Commission Rate') }}</th>
                            <th>{{ __('Current Due') }}</th>
                            <th>{{ __('Limits (Max / Min)') }}</th>
                            <th>{{ __('Billing Day') }}</th>
                            <th>{{ __('Marketplace Status') }}</th>
                            <th>{{ __('Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($commissions as $comm)
                            @php
                                $shop = $comm->tenant;
                                $isOverLimit = $comm->isOverdueLimit();
                            @endphp
                            <tr>
                                <td>
                                    <b>{{ $shop?->shop_name ?? $comm->tenant_id }}</b>
                                    <br>
                                    <code>{{ $comm->tenant_id }}</code>
                                    <br>
                                    <small class="text-muted"><i class="fas fa-user fa-xs"></i> {{ $shop?->owner_name }} ({{ $shop?->phone }})</small>
                                </td>
                                <td>
                                    <span class="badge badge-primary px-2 py-1 font-weight-bold">
                                        {{ $comm->commission_rate }}%
                                    </span>
                                </td>
                                <td>
                                    <h6 class="font-weight-bold {{ $isOverLimit ? 'text-danger' : 'text-dark' }} mb-0">
                                        ৳{{ number_format($comm->current_due, 2) }}
                                    </h6>
                                    @if($isOverLimit)
                                        <span class="badge badge-danger">লিমিট অতিক্রম করেছে</span>
                                    @else
                                        <small class="text-success">ক্রেডিট বাকি: ৳{{ number_format($comm->availableCredit(), 2) }}</small>
                                    @endif
                                    <br>
                                    <small class="text-muted">পরিশোধিত: ৳{{ number_format($comm->total_paid_commission, 2) }}</small>
                                </td>
                                <td>
                                    <span class="badge badge-light border text-danger">
                                        <b>Max:</b> ৳{{ number_format($comm->max_due_limit, 2) }}
                                    </span>
                                    <br>
                                    <span class="badge badge-light border text-info mt-1">
                                        <b>Min:</b> ৳{{ number_format($comm->min_due_limit, 2) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-light border">
                                        মাসের <b>{{ $comm->billing_day }}</b> তারিখ
                                    </span>
                                </td>
                                <td>
                                    @if($comm->marketplace_status === 'active')
                                        <span class="badge badge-success"><i class="fas fa-check-circle mr-1"></i> Active</span>
                                    @elseif($comm->marketplace_status === 'paused_due_limit')
                                        <span class="badge badge-warning text-dark"><i class="fas fa-pause-circle mr-1"></i> Paused (Overdue)</span>
                                    @else
                                        <span class="badge badge-danger"><i class="fas fa-ban mr-1"></i> Suspended</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <button type="button" class="btn btn-sm btn-outline-primary" data-toggle="modal" data-target="#editModal-{{ $comm->id }}" title="{{ __('Edit Limits') }}">
                                            <i class="fas fa-cog"></i>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-outline-success" data-toggle="modal" data-target="#payModal-{{ $comm->id }}" title="{{ __('Record Payment / Adjustment') }}">
                                            <i class="fas fa-hand-holding-usd"></i>
                                        </button>
                                        <a href="{{ route('back.commissions.logs', ['tenant_id' => $comm->tenant_id]) }}" class="btn btn-sm btn-outline-info" title="{{ __('View Ledger') }}">
                                            <i class="fas fa-history"></i>
                                        </a>
                                    </div>

                                    <!-- Edit Limits Modal -->
                                    <div class="modal fade" id="editModal-{{ $comm->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('back.commissions.update-limits', $comm->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-header bg-primary text-white">
                                                        <h5 class="modal-title font-weight-bold">
                                                            <i class="fas fa-cog mr-2"></i> {{ __('Edit Commission & Limits: ') }} {{ $shop?->shop_name }}
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Commission Rate (%)') }}</label>
                                                            <input type="number" step="0.01" min="0" max="100" name="commission_rate" class="form-control" value="{{ $comm->commission_rate }}" required>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Max Due Limit (৳) - Hard Stop') }}</label>
                                                            <input type="number" step="1" min="0" name="max_due_limit" class="form-control" value="{{ $comm->max_due_limit }}" required>
                                                            <small class="text-muted">{{ __('If due reaches this amount, shop auto-pauses on marketplace') }}</small>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Min Due Limit (৳) - Monthly Billing') }}</label>
                                                            <input type="number" step="1" min="0" name="min_due_limit" class="form-control" value="{{ $comm->min_due_limit }}" required>
                                                            <small class="text-muted">{{ __('If due on billing day is less than this, it rolls over') }}</small>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Monthly Billing Day') }}</label>
                                                            <select name="billing_day" class="form-control">
                                                                @for($d = 1; $d <= 28; $d++)
                                                                    <option value="{{ $d }}" {{ $comm->billing_day == $d ? 'selected' : '' }}>
                                                                        মাসের {{ $d }} তারিখ
                                                                    </option>
                                                                @endfor
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Marketplace Status') }}</label>
                                                            <select name="marketplace_status" class="form-control">
                                                                <option value="active" {{ $comm->marketplace_status === 'active' ? 'selected' : '' }}>Active (মার্কেটপ্লেসে দৃশ্যমান)</option>
                                                                <option value="paused_due_limit" {{ $comm->marketplace_status === 'paused_due_limit' ? 'selected' : '' }}>Paused (বকেয়ার কারণে পজ)</option>
                                                                <option value="suspended" {{ $comm->marketplace_status === 'suspended' ? 'selected' : '' }}>Suspended (স্থগিত)</option>
                                                            </select>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-primary">{{ __('Save Changes') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Record Payment / Adjustment Modal -->
                                    <div class="modal fade" id="payModal-{{ $comm->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered" role="document">
                                            <div class="modal-content">
                                                <form action="{{ route('back.commissions.record-payment', $comm->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header bg-success text-white">
                                                        <h5 class="modal-title font-weight-bold">
                                                            <i class="fas fa-hand-holding-usd mr-2"></i> {{ __('Record Payment / Adjustment') }}
                                                        </h5>
                                                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                            <span aria-hidden="true">&times;</span>
                                                        </button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div class="alert alert-light border mb-3">
                                                            <b>{{ $shop?->shop_name }}</b> | বর্তমান বকেয়া: <b class="text-danger">৳{{ number_format($comm->current_due, 2) }}</b>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Action Type') }}</label>
                                                            <select name="type" class="form-control" required>
                                                                <option value="payment">টাকা পরিশোধ (Payment Received - বকেয়া কমবে)</option>
                                                                <option value="adjustment_credit">ছাড় / ক্রেডিট (Waiver - বকেয়া কমবে)</option>
                                                                <option value="adjustment_debit">জরিমানা / ডেবিট (Penalty - বকেয়া বাড়বে)</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Amount (৳)') }} *</label>
                                                            <input type="number" step="0.01" min="1" name="amount" class="form-control" placeholder="0.00" required>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Payment Method') }}</label>
                                                            <select name="payment_method" class="form-control">
                                                                <option value="bkash">bKash</option>
                                                                <option value="nagad">Nagad</option>
                                                                <option value="bank">Bank Transfer</option>
                                                                <option value="cash">Cash / Offline</option>
                                                            </select>
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Transaction ID (TrxID)') }}</label>
                                                            <input type="text" name="trx_id" class="form-control" placeholder="e.g. 9AK29L0Q1">
                                                        </div>

                                                        <div class="form-group">
                                                            <label class="font-weight-bold">{{ __('Notes / Memo') }}</label>
                                                            <input type="text" name="notes" class="form-control" placeholder="e.g. Cleared via bKash Merchant">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
                                                        <button type="submit" class="btn btn-success">{{ __('Submit & Update Balance') }}</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    {{ __('No shop commission records found.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

@endsection
