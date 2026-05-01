@extends('master.back')

@section('content')

<div class="container-fluid">

	<!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Create Police Station') }}</b> </h3>
                <a class="btn btn-primary btn-sm" href="{{route('back.police_station.index')}}"><i class="fas fa-chevron-left"></i> {{ __('Back') }}</a>
            </div>
        </div>
    </div>

	<!-- Form -->
	<div class="row">
		<div class="col-xl-12 col-lg-12 col-md-12">
			<div class="card o-hidden border-0 shadow-lg">
				<div class="card-body ">
					<!-- Nested Row within Card Body -->
					<div class="row justify-content-center">
						<div class="col-lg-12">
								<form class="admin-form" action="{{ route('back.police_station.store') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
									@include('alerts.alerts')

									<div class="form-group">
										<label for="district_id">{{ __('District') }} *</label>
										<select name="district_id" id="district_id" class="form-control" required>
                                            <option value="" disabled selected>{{ __('Select District') }}</option>
                                            @foreach($districts as $district)
                                            <option value="{{ $district->id }}">{{ $district->name }}</option>
                                            @endforeach
                                        </select>
									</div>

									<div class="form-group">
										<label for="name">{{ __('Police Station Name') }} *</label>
										<input type="text" name="name" class="form-control" id="name" placeholder="{{ __('Enter Police Station Name') }}" value="{{ old('name') }}" required>
									</div>
                                    
									<div class="form-group">
										<button type="submit" class="btn btn-secondary">{{ __('Submit') }}</button>
									</div>

								</form>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>

</div>

@endsection
