@if (Session::has('success'))
<div class="alert alert-success alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <b>{{ Session::get('success') }}</b>
</div>
@endif
@if (Session::has('error'))
<div class="alert alert-danger alert-dismissible">
      <button type="button" class="close" data-dismiss="alert">&times;</button>
      <b>{{ Session::get('error') }}</b>
</div>
@endif
@if(isset($errors) && $errors->any())
<div class="alert alert-danger validation">
	<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
			aria-hidden="true">×</span></button>
	<ul class="text-left {{ $errors->count() == 1 ? 'list-unstyled' : '' }}">
		@foreach($errors->all() as $error)
		<li>
            <b>{{$error}}</b>
        </li>
		@endforeach
	</ul>
</div>
@endif
