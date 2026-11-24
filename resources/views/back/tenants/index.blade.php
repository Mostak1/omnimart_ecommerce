@extends('master.back')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <div>
                    <h3 class="mb-0 bc-title"><b>{{ __('Manage Shops / Tenants (SaaS Platform)') }}</b></h3>
                    <p class="text-muted small mb-0">{{ __('সকল টেন্যান্ট শপ, ডাটাবেস আইসোলেশন ও সাবস্ক্রিপশন প্ল্যান নিয়ন্ত্রণ') }}</p>
                </div>
                <a class="btn btn-primary btn-sm" href="{{ route('back.tenants.create') }}">
                    <i class="fas fa-plus"></i> {{ __('Open New Shop') }}
                </a>
            </div>
        </div>
    </div>

    <!-- SaaS Metrics Overview Cards -->
    <div class="row mb-4">
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">মোট দোকান সংখ্যা</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalShops ?? count($tenants) }} টি</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-store fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">সক্রিয় দোকান (Active)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $activeShops ?? 0 }} টি</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">মেয়াদ শেষ / দ্রুত নবায়ন</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $expiringSoon ?? 0 }} টি</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">স্থগিত দোকান (Suspended)</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $suspendedShops ?? 0 }} টি</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-ban fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DataTales -->
    <div class="card shadow mb-4">
        <div class="card-body">
            @include('alerts.alerts')

            <div class="gd-responsive-table">
                <table class="table table-bordered table-striped" id="admin-table" width="100%" cellspacing="0">
                    <thead>
                        <tr>
                            <th>{{ __('Shop Name') }}</th>
                            <th>{{ __('Subdomain & Store Link') }}</th>
                            <th>{{ __('Owner & Phone') }}</th>
                            <th>{{ __('Database') }}</th>
                            <th>{{ __('SaaS Plan & Expiry') }}</th>
                            <th>{{ __('Status') }}</th>
                            <th style="width: 140px;">{{ __('Actions') }}</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($tenants as $tenant)
                            @php
                                $domainRecord = $tenant->domains->first();
                                $subdomain = $domainRecord ? $domainRecord->domain : $tenant->id;
                                $port = request()->getPort();
                                $portSuffix = ($port && !in_array($port, [80, 443])) ? ":{$port}" : '';
                                $fullUrl = "http://{$subdomain}.{$centralDomain}{$portSuffix}";
                                $isExp = $tenant->isExpired();
                                $days = $tenant->daysRemaining();
                            @endphp
                            <tr>
                                <td>
                                    <b>{{ $tenant->shop_name }}</b>
                                    <br>
                                    <span class="badge badge-secondary text-uppercase">{{ $tenant->shop_type ?: 'General' }}</span>
                                </td>
                                <td>
                                    <code>{{ $subdomain }}.{{ $centralDomain }}</code>
                                    <br>
                                    <a href="{{ $fullUrl }}" target="_blank" class="btn btn-xs btn-outline-info mt-1">
                                        <i class="fas fa-external-link-alt"></i> {{ __('Visit Shop') }}
                                    </a>
                                </td>
                                <td>
                                    <strong>{{ $tenant->owner_name }}</strong>
                                    <br>
                                    <a href="tel:{{ $tenant->phone }}" class="text-muted"><i class="fas fa-phone fa-xs"></i> {{ $tenant->phone }}</a>
                                </td>
                                <td>
                                    <span class="badge badge-light border">
                                        <i class="fas fa-database text-primary"></i> {{ $tenant->database()->getName() }}
                                    </span>
                                    <br>
                                    @if(($tenant->db_setup_mode ?? '') === 'cpanel')
                                        <span class="badge badge-info mt-1"><i class="fas fa-server"></i> cPanel DB</span>
                                    @else
                                        <span class="badge badge-light text-muted border mt-1"><i class="fas fa-robot"></i> Auto DB</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-primary">{{ $tenant->plan_name }}</span>
                                    <br>
                                    @if($tenant->expires_at)
                                        <small class="text-muted">মেয়াদ: {{ $tenant->expires_at->format('d/m/Y') }}</small>
                                        <br>
                                        @if($isExp)
                                            <span class="badge badge-danger">মেয়াদোত্তীর্ণ</span>
                                        @elseif($days <= 7)
                                            <span class="badge badge-warning text-dark">{{ $days }} দিন বাকি</span>
                                        @else
                                            <span class="badge badge-success">{{ $days }} দিন বাকি</span>
                                        @endif
                                    @else
                                        <small class="text-muted">মেয়াদ: আজীবন</small>
                                    @endif
                                    <br>
                                    <button type="button" class="btn btn-xs btn-outline-primary mt-1" data-toggle="modal" data-target="#subModal-{{ $tenant->id }}">
                                        <i class="fas fa-calendar-check mr-1"></i> রিনিউ / প্ল্যান
                                    </button>
                                </td>
                                <td>
                                    <div class="dropdown">
                                        <button class="btn btn-{{ $tenant->status === 'active' ? 'success' : 'danger' }} btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            {{ ucfirst($tenant->status) }}
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('back.tenants.status', [$tenant->id, 'active']) }}">
                                                <i class="fas fa-check text-success mr-2"></i> {{ __('Active') }}
                                            </a>
                                            <a class="dropdown-item" href="{{ route('back.tenants.status', [$tenant->id, 'suspended']) }}">
                                                <i class="fas fa-ban text-danger mr-2"></i> {{ __('Suspend') }}
                                            </a>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="action-list d-flex align-items-center">
                                        <a href="{{ $fullUrl }}/tenant-admin" target="_blank" class="btn btn-info btn-sm mr-1" title="{{ __('Store Admin Portal') }}">
                                            <i class="fas fa-user-shield"></i>
                                        </a>
                                        <a class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" href="javascript:;" data-href="{{ route('back.tenants.destroy', $tenant->id) }}" title="{{ __('Delete Tenant') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>

                            <!-- Subscription Management Modal for each tenant -->
                            <div class="modal fade" id="subModal-{{ $tenant->id }}" tabindex="-1" role="dialog" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered" role="document">
                                    <div class="modal-content">
                                        <form action="{{ route('back.tenants.subscription', $tenant->id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <div class="modal-header bg-primary text-white">
                                                <h5 class="modal-title font-weight-bold">
                                                    <i class="fas fa-calendar-check mr-2"></i> সাবস্ক্রিপশন প্ল্যান: {{ $tenant->shop_name }}
                                                </h5>
                                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                                    <span aria-hidden="true">&times;</span>
                                                </button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold">প্যাকেজ প্ল্যান নির্বাচন</label>
                                                    <select name="plan_name" class="form-control" required>
                                                        <option value="Starter (ফ্রি ট্রায়াল)" {{ $tenant->plan_name == 'Starter (ফ্রি ট্রায়াল)' ? 'selected' : '' }}>Starter (ফ্রি ট্রায়াল)</option>
                                                        <option value="Standard (৳৯৯৯/মাস)" {{ $tenant->plan_name == 'Standard (৳৯৯৯/মাস)' ? 'selected' : '' }}>Standard (৳৯৯৯/মাস)</option>
                                                        <option value="Premium (৳১৯৯৯/মাস)" {{ $tenant->plan_name == 'Premium (৳১৯৯৯/মাস)' ? 'selected' : '' }}>Premium (৳১৯৯৯/মাস)</option>
                                                        <option value="Enterprise (বার্ষিক প্ল্যান)" {{ $tenant->plan_name == 'Enterprise (বার্ষিক প্ল্যান)' ? 'selected' : '' }}>Enterprise (বার্ষিক প্ল্যান)</option>
                                                    </select>
                                                </div>

                                                <div class="form-group mb-3">
                                                    <label class="font-weight-bold">দ্রুত মেয়াদ বৃদ্ধি করুন (Extend Days)</label>
                                                    <div class="btn-group btn-group-toggle w-100" data-toggle="buttons">
                                                        <label class="btn btn-outline-secondary active">
                                                            <input type="radio" name="extend_days" value="30" checked> +৩০ দিন (১ মাস)
                                                        </label>
                                                        <label class="btn btn-outline-secondary">
                                                            <input type="radio" name="extend_days" value="90"> +৯০ দিন (৩ মাস)
                                                        </label>
                                                        <label class="btn btn-outline-secondary">
                                                            <input type="radio" name="extend_days" value="365"> +১ বছর
                                                        </label>
                                                    </div>
                                                </div>

                                                <div class="form-group mb-0">
                                                    <label class="font-weight-bold">অথবা নির্দিষ্ট মেয়াদের তারিখ নির্ধারণ করুন</label>
                                                    <input type="date" name="expires_at" class="form-control" value="{{ $tenant->expires_at ? $tenant->expires_at->format('Y-m-d') : '' }}">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-dismiss="modal">বাতিল</button>
                                                <button type="submit" class="btn btn-primary font-weight-bold">
                                                    <i class="fas fa-check mr-1"></i> সাবস্ক্রিপশন নবায়ন করুন
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-store-slash fa-2x mb-2 d-block"></i>
                                    {{ __('No shops/tenants found yet. Click "Open New Shop" to provision your first store!') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- DELETE CONFIRMATION MODAL --}}
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="confirm-deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ __('Confirm Tenant Deletion?') }}</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    {{ __('Caution: Deleting this tenant will permanently drop its dedicated MySQL database and all associated store data. This action cannot be undone!') }}
                </div>
                {{ __('Do you want to proceed?') }}
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">{{ __('Cancel') }}</button>
                <form action="" class="d-inline btn-ok" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">{{ __('Delete Permanently') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
