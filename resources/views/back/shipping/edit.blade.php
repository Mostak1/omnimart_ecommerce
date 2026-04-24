@extends('master.back')

@section('content')

<div class="container-fluid">

	<!-- Shipping Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class=" mb-0 "><b>{{ __('Update Shipping Charge') }}</b> </h3>

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
								<form class="admin-form" action="{{ route('back.shipping.update',$shipping->id) }}"
									method="POST" enctype="multipart/form-data">

                                    @csrf

                                    @method('PUT')

									@include('alerts.alerts')

									<div class="form-group">
										<label for="title">{{ __('Title') }} *</label>
										<input type="text" name="title" class="form-control" id="title"
											placeholder="{{ __('Enter Title') }}" value="{{ $shipping->title }}" >
									</div>

                                    <div class="form-group">
                                        <label for="is_automated" class="text-left">
                                            <input type="checkbox" name="is_automated" {{ $shipping->is_automated ? 'checked' : '' }} class="my-2" id="is_automated">
                                            {{ __('Use Automated District Shipping') }}
                                        </label>
                                        <small class="d-block text-info">{{ __('Set the default rate here, then override only the districts that need custom pricing.') }}</small>
                                    </div>

									@if ($shipping->id ==1)
									<div class="form-group">
                                        <label for="price">{{ __('Minimum Order Amount') }} *</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span
                                                    class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="price"
                                                name="minimum_price" class="form-control"
                                                placeholder="{{ __('Enter Price') }}"
                                                value="{{ PriceHelper::setPrice($shipping->minimum_price) }}" >
                                        </div>
										<label for="is_condition" class="text-left">
											<input type="checkbox" name="is_condition" {{$shipping->is_condition == 1 ? 'checked' : ''}} class="my-2" id="is_condition">
										{{__('Condition Free Shipping')}}
										</label>
                                    </div>
									@else
									<div class="form-group">
                                        <label for="price">{{ __('Shipping Charge') }} *</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span
                                                    class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="price"
                                                name="price" class="form-control"
                                                placeholder="{{ __('Enter Price') }}"
                                                value="{{ PriceHelper::setPrice($shipping->price) }}" >
                                        </div>
                                    </div>
									@endif

                                    <div class="form-group">
                                        <label for="default_base_shipping_charge">{{ __('Default / All Base Shipping Charge') }}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="default_base_shipping_charge" name="default_base_shipping_charge" class="form-control"
                                                placeholder="{{ __('Enter Default Base Shipping Charge') }}"
                                                value="{{ PriceHelper::setPrice($shipping->default_base_shipping_charge ?? $shipping->outside_dhaka_price ?? 0) }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label for="default_per_kg_extra_charge">{{ __('Default / All Per KG Extra Charge') }}</label>
                                        <div class="input-group mb-3">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">{{ PriceHelper::adminCurrency() }}</span>
                                            </div>
                                            <input type="text" id="default_per_kg_extra_charge" name="default_per_kg_extra_charge" class="form-control"
                                                placeholder="{{ __('Enter Default Per KG Extra Charge') }}"
                                                value="{{ PriceHelper::setPrice($shipping->default_per_kg_extra_charge ?? $shipping->per_kg_price ?? 0) }}">
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label>{{ __('District Overrides') }}</label>
                                        <small class="d-block text-info mb-3">{{ __('Leave a district field blank to use the Default / All rate.') }}</small>
                                        <div class="table-responsive">
                                            <table class="table table-bordered table-sm">
                                                <thead>
                                                    <tr>
                                                        <th>{{ __('District') }}</th>
                                                        <th>{{ __('Base Shipping Charge') }}</th>
                                                        <th>{{ __('Per KG Extra Charge') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($districts as $district)
                                                        <tr>
                                                            <td>
                                                                {{ $district->name }}
                                                                <input type="hidden" name="district_id[]" value="{{ $district->id }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="district_base_shipping_charge[]" class="form-control"
                                                                    value="{{ $district->base_shipping_charge !== null ? PriceHelper::setPrice($district->base_shipping_charge) : '' }}"
                                                                    placeholder="{{ __('Use default') }}">
                                                            </td>
                                                            <td>
                                                                <input type="text" name="district_per_kg_extra_charge[]" class="form-control"
                                                                    value="{{ $district->per_kg_extra_charge !== null ? PriceHelper::setPrice($district->per_kg_extra_charge) : '' }}"
                                                                    placeholder="{{ __('Use default') }}">
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                  

								

									<div class="form-group">
										<button type="submit" class="btn btn-secondary ">{{ __('Submit') }}</button>
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
