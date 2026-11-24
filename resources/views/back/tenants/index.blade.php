@extends('master.back')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Manage Shops / Tenants') }}</b></h3>
                <a class="btn btn-primary btn-sm" href="{{ route('back.tenants.create') }}">
                    <i class="fas fa-plus"></i> {{ __('Open New Shop') }}
                </a>
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
                            <th>{{ __('Status') }}</th>
                            <th>{{ __('Actions') }}</th>
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
                                    @if($tenant->address)
                                        <br><small class="text-muted"><i class="fas fa-map-marker-alt fa-xs"></i> {{ $tenant->address }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-light border">
                                        <i class="fas fa-database text-primary"></i> {{ $tenant->database()->getName() }}
                                    </span>
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
                                        <a href="{{ $fullUrl }}/tenant-test" target="_blank" class="btn btn-secondary btn-sm mr-1" title="{{ __('Test Subdomain Route') }}">
                                            <i class="fas fa-vial"></i>
                                        </a>
                                        <a class="btn btn-danger btn-sm" data-toggle="modal" data-target="#confirm-delete" href="javascript:;" data-href="{{ route('back.tenants.destroy', $tenant->id) }}" title="{{ __('Delete Tenant') }}">
                                            <i class="fas fa-trash-alt"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
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

