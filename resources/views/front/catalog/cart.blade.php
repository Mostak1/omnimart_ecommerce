@extends('master.front')
@section('title')
    {{__('Cart')}}
@endsection
@section('meta')
<meta name="keywords" content="{{$setting->meta_keywords}}">
<meta name="description" content="{{$setting->meta_description}}">
@endsection
@section('content')
    <!-- Page Title-->
@if (data_get($site_visibility, 'cart_breadcrumb', 1))
<div class="page-title">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <ul class="breadcrumbs">
                    <li><a href="{{route('front.index')}}">{{__('Home')}}</a> </li>
                    <li class="separator"></li>
                    <li>{{__('Cart')}}</li>
                  </ul>
            </div>
        </div>
    </div>
  </div>
@endif

  @if(Session::has('cart') && count(Session::get('cart')) > 0)
  <div class="container  padding-bottom-3x mb-1">

    <!-- Shopping Cart-->
    @if (data_get($site_visibility, 'cart_table', 1) || data_get($site_visibility, 'cart_summary', 1))
    <div id="view_cart_load">
        @include('includes.cart')
    </div>
    @endif

</div>
  @else
  <div class="container  padding-bottom-2x">
    <div class="card text-center">
      <div class="card-body">
        <h3 class="card-title">{{__('Your shopping cart is empty.')}}</h3>
       <a class="btn btn-outline-primary m-4" href="{{route('front.catalog')}}"><i class="icon-package pr-2"></i>{{__('View our products')}}</a></div>
      </div>
    </div>
  @endif
  <!-- Page Content-->


@endsection
