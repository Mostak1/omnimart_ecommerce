@extends('master.back')

@section('content')

<div class="container-fluid">

	<!-- Shipping Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class=" mb-0 "><b>{{ __('Create Shipping') }}</b> </h3>
                <a class="btn btn-primary btn-sm" href="{{route('back.shipping.index')}}"><i class="fas fa-chevron-left"></i> {{ __('Back') }}</a>
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
								<form class="admin-form" action="{{ route('back.shipping.store') }}" method="POST"
									enctype="multipart/form-data">

                                    @csrf

									@include('alerts.alerts')

									<div class="form-group">
										<label for="title">{{ __('Title') }} *</label>
										<input type="text" name="title" class="form-control" id="title"
											placeholder="{{ __('Enter Title') }}" value="{{ old('title') }}" >
									</div>

                                    <div class="form-group">
                                        <label for="price">{{ __('Shipping Cost') }} *</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span
                                                    class="input-group-text">{{ PriceHelper::adminCurrency() }}
                                                </span>
                                            </div>
                                            <input type="text" id="price"
                                                name="price" class="form-control"
                                                placeholder="{{ __('Enter Price') }}"

                                                value="{{ old('price') }}" >
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="is_automated" class="text-left">
                                            <input type="checkbox" name="is_automated" {{ old('is_automated') ? 'checked' : '' }} class="my-2" id="is_automated">
                                            {{ __('Use Automated District Shipping') }}
                                        </label>
                                        <small class="d-block text-info">{{ __('Dhaka = base rate, outside Dhaka = outside rate, every extra KG adds extra rate.') }}</small>
                                    </div>

                                    <div class="form-group">
                                        <label for="dhaka_price">{{ __('Dhaka Shipping Cost') }}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="dhaka_price" name="dhaka_price" class="form-control"
                                                placeholder="{{ __('Enter Dhaka Shipping Cost') }}" value="{{ old('dhaka_price', 80) }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="outside_dhaka_price">{{ __('Outside Dhaka Shipping Cost') }}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="outside_dhaka_price" name="outside_dhaka_price" class="form-control"
                                                placeholder="{{ __('Enter Outside Dhaka Shipping Cost') }}" value="{{ old('outside_dhaka_price', 130) }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="per_kg_price">{{ __('Extra Charge Per KG') }}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="per_kg_price" name="per_kg_price" class="form-control"
                                                placeholder="{{ __('Enter Extra Charge Per KG') }}" value="{{ old('per_kg_price', 30) }}">
                                        </div>
                                    </div>
								

									<div class="form-group">
										<button type="submit" class="btn btn-secondary btn-block">{{ __('Submit') }}</button>
									</div>

									<div>
								</form>
						</div>
					</div>
				</div>
			</div>

		</div>

	</div>

</div>

@endsection
