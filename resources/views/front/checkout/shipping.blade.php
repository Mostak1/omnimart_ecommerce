@extends('master.front')

@section('title')
    {{ __('Shipping') }}
@endsection
@section('content')
    <!-- Page Title-->
    @if (data_get($site_visibility, 'checkout_breadcrumb', 1))
    <div class="page-title">
        <div class="container">
            <div class="column">
                <ul class="breadcrumbs">
                    <li><a href="{{ route('front.index') }}">{{ __('Home') }}</a> </li>
                    <li class="separator"></li>
                    <li>{{ __('Shipping address') }}</li>
                </ul>
            </div>
        </div>
    </div>
    @endif
    <!-- Page Content-->
    <div class="container padding-bottom-3x mb-1  checkut-page">
        <div class="row">
            <!-- Shipping Adress-->
            @if (data_get($site_visibility, 'checkout_billing_form', 1))
            <div class="col-xl-9 col-lg-8">
                <div class="steps flex-sm-nowrap mb-5">
                    <a class="step" href="{{ route('front.checkout.billing') }}">
                        <h4 class="step-title">1. {{ __('Billing Address') }}:</h4>
                    </a>
                    <a class="step active" href="javascript:;">
                        <h4 class="step-title">2. {{ __('Shipping Address') }}:</h4>
                    </a>
                    <a class="step" href="{{ route('front.checkout.payment') }}">
                        <h4 class="step-title">3. {{ __('Review and pay') }}</h4>
                    </a>
                </div>
                <div class="card">
                    <div class="card-body">
                        <h6>{{ __('Shipping Address') }}</h6>

                        <form id="checkoutShipping" action="{{ route('front.checkout.shipping.store') }}" method="POST">
                            @csrf
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="checkout-fn">{{ __('Name') }}*</label>
                                        <input class="form-control {{ $errors->has('ship_first_name') ? 'requireInput' : '' }}" name="ship_first_name" type="text" id="checkout-fn"
                                            value="{{ isset($user) ? $user->first_name : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="checkout-email">{{ __('E-mail Address') }}</label>
                                        <input class="form-control {{ $errors->has('ship_email') ? 'requireInput' : '' }}" name="ship_email" type="email" id="checkout-email"
                                            value="{{ isset($user) ? $user->email : '' }}">
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="checkout-phone">{{ __('Phone Number') }}*</label>
                                        <input class="form-control {{ $errors->has('ship_phone') ? 'requireInput' : '' }}" name="ship_phone" type="text" id="checkout-phone"
                                            value="{{ isset($user) ? $user->phone : '' }}">
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="form-group">
                                        <label for="checkout-address1">{{ __('Address') }} *</label>
                                        <input class="form-control {{ $errors->has('ship_address1') ? 'requireInput' : '' }}" name="ship_address1"  type="text"
                                            id="checkout-address1" value="{{ isset($user) ? $user->ship_address1 : '' }}">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="shipping-country">{{ __('District') }} *</label>
                                        <select class="form-control {{ $errors->has('ship_country') ? 'requireInput' : '' }}" name="ship_country"  id="shipping-country" required data-shipping-url="{{ route('front.shipping.setup') }}">
                                            <option value="" selected disabled>{{ __('Choose District') }}</option>
                                            @foreach ($districts as $district)
                                                <option value="{{ $district->name }}"
                                                    {{ isset($user) && $user->ship_country == $district->name ? 'selected' : '' }}>
                                                    {{ $district->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div class="form-group">
                                        <label for="shipping-thana">{{ __('Police Station') }} *</label>
                                        <select class="form-control {{ $errors->has('ship_thana') ? 'requireInput' : '' }}" name="ship_thana" id="shipping-thana" required>
                                            <option value="" selected disabled>{{ __('Select Police Station') }}</option>
                                            @if(isset($user) && $user->ship_thana)
                                                <option value="{{ $user->ship_thana }}" selected>{{ $user->ship_thana }}</option>
                                            @endif
                                        </select>
                                    </div>
                                </div>
                            </div>


                            <div class="d-flex justify-content-between paddin-top-1x mt-4">
                                <a class="btn btn-primary btn-sm" href="{{ route('front.cart') }}"><span
                                        class="hidden-xs-down"><i class="icon-arrow-left"></i>
                                        {{ __('Back To Cart') }}</span>
                                </a><button class="btn btn-primary  btn-sm" type="submit"><span
                                        class="hidden-xs-down">{{ __('Continue') }}</span><i
                                        class="icon-arrow-right"></i></button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            @endif
            <!-- Sidebar  -->
            @if (data_get($site_visibility, 'checkout_order_summary', 1))
            <div class="col-xl-3 col-lg-4">
                @include('includes.checkout_sitebar', $cart)
            </div>
            @endif
        </div>
    </div>
@endsection

@section('script')
<script>
    $(document).ready(function() {
        $('#shipping-country').on('change', function() {
            var district = $(this).val();
            var $thanaSelect = $('#shipping-thana');
            $thanaSelect.empty();
            $thanaSelect.append('<option value="" selected disabled>{{ __("Loading...") }}</option>');
            
            if(district) {
                $.ajax({
                    url: '{{ url("/get-police-stations") }}/' + encodeURIComponent(district),
                    type: "GET",
                    dataType: "json",
                    success:function(data) {
                        $thanaSelect.empty();
                        $thanaSelect.append('<option value="" selected disabled>{{ __("Select Police Station") }}</option>');
                        $.each(data, function(key, value) {
                            $thanaSelect.append('<option value="'+ value.name +'">'+ value.name +'</option>');
                        });
                    }
                });
            } else {
                $thanaSelect.empty();
                $thanaSelect.append('<option value="" selected disabled>{{ __("Select Police Station") }}</option>');
            }
        });
    });
</script>
@endsection
