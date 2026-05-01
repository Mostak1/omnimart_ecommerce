@extends('master.back')

@section('content')

<div class="container-fluid">

	<!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Police Stations') }}</b></h3>
                <a class="btn btn-primary btn-sm" href="{{route('back.police_station.create')}}"><i class="fas fa-plus"></i> {{ __('Add') }}</a>
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
							<th>{{ __('District') }}</th>
							<th>{{ __('Police Station Name') }}</th>
							<th>{{ __('Status') }}</th>
							<th>{{ __('Actions') }}</th>
						</tr>
					</thead>
					<tbody>
						@foreach($police_stations as $data)
						<tr>
							<td>
								{{ $data->district ? $data->district->name : '' }}
							</td>
							<td>
								{{ $data->name }}
							</td>
							<td>
								<div class="dropdown">
									<button class="btn btn-{{  $data->status == 1 ? 'success' : 'danger'  }} btn-sm dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{  $data->status == 1 ? __('Active') : __('Deactive')  }}
									</button>
									<div class="dropdown-menu animated--fade-in" aria-labelledby="dropdownMenuButton">
										<a class="dropdown-item" href="{{ route('back.police_station.status',[$data->id,1]) }}">{{ __('Active') }}</a>
										<a class="dropdown-item" href="{{ route('back.police_station.status',[$data->id,0]) }}">{{ __('Deactive') }}</a>
									</div>
								</div>
							</td>
							<td>
								<div class="action-list">
									<a class="btn btn-secondary btn-sm" href="{{ route('back.police_station.edit',$data->id) }}">
										<i class="fas fa-edit"></i>
									</a>
									<a class="btn btn-danger btn-sm " data-toggle="modal" data-target="#confirm-delete" href="javascript:;" data-href="{{ route('back.police_station.destroy',$data->id) }}">
										<i class="fas fa-trash-alt"></i>
									</a>
								</div>
							</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>

</div>

{{-- DELETE MODAL --}}
<div class="modal fade" id="confirm-delete" tabindex="-1" role="dialog" aria-labelledby="confirm-deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title" id="exampleModalLabel">{{ __('Confirm Delete?') }}</h5>
			<button class="close" type="button" data-dismiss="modal" aria-label="Close">
				<span aria-hidden="true">×</span>
			</button>
		</div>
		<div class="modal-body">
			{{ __('You are going to delete this police station. All contents related with this police station will be lost.') }} {{ __('Do you want to delete it?') }}
		</div>
		<div class="modal-footer">
			<button type="button" class="btn btn-secondary" data-dismiss="modal">{{ __('Cancel') }}</button>
			<form action="" class="d-inline btn-ok" method="POST">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger">{{ __('Delete') }}</button>
			</form>
		</div>
      </div>
    </div>
  </div>
{{-- DELETE MODAL ENDS --}}

@endsection
