@extends('master.back')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="d-sm-flex align-items-center justify-content-between">
                <h3 class="mb-0 bc-title"><b>{{ __('Open New Shop / Tenant') }}</b></h3>
                <a class="btn btn-primary btn-sm" href="{{ route('back.tenants.index') }}">
                    <i class="fas fa-chevron-left"></i> {{ __('Back') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Form -->
    <div class="row">
        <div class="col-xl-12 col-lg-12 col-md-12">
            <div class="card o-hidden border-0 shadow-lg">
                <div class="card-body">
                    <form class="admin-form" action="{{ route('back.tenants.store') }}" method="POST">
                        @csrf
                        @include('alerts.alerts')

                        <div class="row">
                            <!-- Shop Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="shop_name">{{ __('Shop / Business Name') }} *</label>
                                    <input type="text" name="shop_name" class="form-control" id="shop_name"
                                        placeholder="{{ __('e.g. Bhai Bhai Fashion') }}" value="{{ old('shop_name') }}" required>
                                </div>
                            </div>

                            <!-- Subdomain -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="subdomain">{{ __('Subdomain (Slug)') }} *</label>
                                    <div class="input-group">
                                        <input type="text" name="subdomain" class="form-control" id="subdomain"
                                            placeholder="{{ __('e.g. bhaibhai') }}" value="{{ old('subdomain') }}" required>
                                        <div class="input-group-append">
                                            <span class="input-group-text bg-light text-primary font-weight-bold">
                                                .{{ $centralDomain }}
                                            </span>
                                        </div>
                                    </div>
                                    <small class="form-text text-muted">
                                        {{ __('Only lowercase letters, numbers, and hyphens (min 3 chars). Database db_tenant_<subdomain> will be created.') }}
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Owner Name -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="owner_name">{{ __('Store Owner Name') }} *</label>
                                    <input type="text" name="owner_name" class="form-control" id="owner_name"
                                        placeholder="{{ __('e.g. Mohammad Ali') }}" value="{{ old('owner_name') }}" required>
                                </div>
                            </div>

                            <!-- Phone -->
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="phone">{{ __('Mobile / Phone Number') }} *</label>
                                    <input type="text" name="phone" class="form-control" id="phone"
                                        placeholder="{{ __('e.g. 01711000000') }}" value="{{ old('phone') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Shop Type -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="shop_type">{{ __('Shop Category / Type') }}</label>
                                    <select name="shop_type" id="shop_type" class="form-control">
                                        <option value="fashion" {{ old('shop_type') == 'fashion' ? 'selected' : '' }}>{{ __('Fashion & Clothing') }}</option>
                                        <option value="electronics" {{ old('shop_type') == 'electronics' ? 'selected' : '' }}>{{ __('Electronics & Gadgets') }}</option>
                                        <option value="grocery" {{ old('shop_type') == 'grocery' ? 'selected' : '' }}>{{ __('Grocery & Food') }}</option>
                                        <option value="pharmacy" {{ old('shop_type') == 'pharmacy' ? 'selected' : '' }}>{{ __('Pharmacy & Healthcare') }}</option>
                                        <option value="footwear" {{ old('shop_type') == 'footwear' ? 'selected' : '' }}>{{ __('Footwear') }}</option>
                                        <option value="general" {{ old('shop_type') == 'general' ? 'selected' : '' }}>{{ __('General Retail') }}</option>
                                    </select>
                                </div>
                            </div>

                            <!-- District (জেলা) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="district_id">{{ __('District (জেলা)') }}</label>
                                    <select name="district_id" id="district_id" class="form-control select2">
                                        <option value="">{{ __('Select District...') }}</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}" data-name="{{ $district->name }}" {{ old('district_id') == $district->id ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            <!-- Police Station (থানা) -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="police_station_id">{{ __('Police Station (থানা)') }}</label>
                                    <select name="police_station_id" id="police_station_id" class="form-control select2">
                                        <option value="">{{ __('Select District First...') }}</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Detailed Address -->
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="address">{{ __('Shop Address / Location') }}</label>
                                    <input type="text" name="address" class="form-control" id="address"
                                        placeholder="{{ __('e.g. Shop #12, Level #3, City Center Mall') }}" value="{{ old('address') }}">
                                </div>
                            </div>

                            <!-- Admin Password -->
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="password">{{ __('Store Owner Initial Password') }} *</label>
                                    <input type="password" name="password" class="form-control" id="password"
                                        placeholder="{{ __('Minimum 6 characters') }}" required>
                                </div>
                            </div>
                        </div>

                        <!-- Database Setup Mode (Localhost / VPS vs cPanel / Shared Hosting) -->
                        <div class="card bg-light border-primary my-4">
                            <div class="card-header bg-primary text-white d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-database mr-2"></i> {{ __('Database Configuration & Provisioning Mode') }}</h6>
                                <span class="badge badge-light text-primary font-weight-bold">cPanel & Cloud Ready</span>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    {{ __('Choose whether the system should auto-create a new MySQL database or connect to an existing database created in cPanel.') }}
                                </p>

                                <div class="row mb-3">
                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-radio p-3 border rounded bg-white shadow-sm h-100">
                                            <input type="radio" id="db_mode_auto" name="db_mode" value="auto" class="custom-control-input"
                                                {{ old('db_mode', 'auto') == 'auto' ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="db_mode_auto">
                                                <i class="fas fa-robot text-success mr-1"></i> {{ __('Auto Provision Mode (Local / VPS)') }}
                                            </label>
                                            <div class="text-muted small mt-1 pl-4">
                                                {{ __('System automatically executes CREATE DATABASE db_tenant_<subdomain> using central MySQL credentials.') }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6 mb-2">
                                        <div class="custom-control custom-radio p-3 border rounded bg-white shadow-sm h-100">
                                            <input type="radio" id="db_mode_cpanel" name="db_mode" value="cpanel" class="custom-control-input"
                                                {{ old('db_mode') == 'cpanel' ? 'checked' : '' }}>
                                            <label class="custom-control-label font-weight-bold text-dark cursor-pointer" for="db_mode_cpanel">
                                                <i class="fas fa-server text-info mr-1"></i> {{ __('cPanel / Pre-Created Database Mode') }}
                                            </label>
                                            <div class="text-muted small mt-1 pl-4">
                                                {{ __('Use this for cPanel / Shared Hosting where you create the database & user in cPanel first.') }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- cPanel / Custom Database Credentials Container -->
                                <div id="cpanel_db_fields" style="{{ old('db_mode') == 'cpanel' ? '' : 'display: none;' }}">
                                    <div class="alert alert-info py-2 px-3 small mb-3">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        <b>cPanel নোট:</b> cPanel এর <i>MySQL Databases</i> সেকশন থেকে ডেটাবেস এবং ইউজার তৈরি করে নিচে বসান। সিস্টেম ওই ডেটাবেসে কানেক্ট হয়ে সকল টেবিল স্বয়ংক্রিয়ভাবে তৈরি (Migrate) করে নিবে।
                                    </div>

                                    <div class="row">
                                        <!-- Custom Database Name -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="custom_db_name" class="font-weight-bold text-dark">
                                                    {{ __('cPanel Database Name') }} <span class="text-danger">*</span>
                                                </label>
                                                <input type="text" name="custom_db_name" id="custom_db_name" class="form-control"
                                                    placeholder="{{ __('e.g. cpuser_shop1') }}" value="{{ old('custom_db_name') }}">
                                                <small class="text-muted">{{ __('Exact DB name created in cPanel') }}</small>
                                            </div>
                                        </div>

                                        <!-- Custom Database User -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="custom_db_user" class="font-weight-bold text-dark">
                                                    {{ __('cPanel DB Username') }}
                                                </label>
                                                <input type="text" name="custom_db_user" id="custom_db_user" class="form-control"
                                                    placeholder="{{ __('e.g. cpuser_dbuser (Optional)') }}" value="{{ old('custom_db_user') }}">
                                                <small class="text-muted">{{ __('Leave blank to use default .env user') }}</small>
                                            </div>
                                        </div>

                                        <!-- Custom Database Password -->
                                        <div class="col-md-4">
                                            <div class="form-group">
                                                <label for="custom_db_pass" class="font-weight-bold text-dark">
                                                    {{ __('cPanel DB User Password') }}
                                                </label>
                                                <input type="password" name="custom_db_pass" id="custom_db_pass" class="form-control"
                                                    placeholder="{{ __('DB password (Optional)') }}">
                                                <small class="text-muted">{{ __('Leave blank to use default .env password') }}</small>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Marketplace Commission & Dual Due Limits -->
                        <div class="card bg-light border-warning my-4">
                            <div class="card-header bg-warning text-dark d-flex align-items-center justify-content-between">
                                <h6 class="mb-0 font-weight-bold"><i class="fas fa-percentage mr-2"></i> {{ __('Mega Marketplace Commission & Dual Due Limits') }}</h6>
                                <span class="badge badge-dark">Model 2 FinTech Control</span>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small mb-3">
                                    {{ __('Configure the sales commission percentage and dual credit limits (Min & Max) for this shop on the Mega Marketplace.') }}
                                </p>

                                <div class="row">
                                    <!-- Commission Rate (%) -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="commission_rate" class="font-weight-bold text-dark">{{ __('Commission Rate (%)') }} *</label>
                                            <div class="input-group">
                                                <input type="number" step="0.01" min="0" max="100" name="commission_rate" id="commission_rate"
                                                    class="form-control" value="{{ old('commission_rate', '10.00') }}" required>
                                                <div class="input-group-append">
                                                    <span class="input-group-text">%</span>
                                                </div>
                                            </div>
                                            <small class="text-muted">{{ __('E.g. 10% on every marketplace sale') }}</small>
                                        </div>
                                    </div>

                                    <!-- Max Due Limit (Hard Limit) -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="max_due_limit" class="font-weight-bold text-dark">{{ __('Max Due Limit (৳)') }} *</label>
                                            <input type="number" step="1" min="0" name="max_due_limit" id="max_due_limit"
                                                class="form-control" value="{{ old('max_due_limit', '1000') }}" required>
                                            <small class="text-muted">{{ __('Auto-pause shop if due reaches this') }}</small>
                                        </div>
                                    </div>

                                    <!-- Min Due Limit (Billing Threshold) -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="min_due_limit" class="font-weight-bold text-dark">{{ __('Min Due Limit (৳)') }} *</label>
                                            <input type="number" step="1" min="0" name="min_due_limit" id="min_due_limit"
                                                class="form-control" value="{{ old('min_due_limit', '200') }}" required>
                                            <small class="text-muted">{{ __('Due below this rolls over to next month') }}</small>
                                        </div>
                                    </div>

                                    <!-- Monthly Billing Day -->
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="billing_day" class="font-weight-bold text-dark">{{ __('Monthly Billing Day') }} *</label>
                                            <select name="billing_day" id="billing_day" class="form-control">
                                                @for($i = 1; $i <= 28; $i++)
                                                    <option value="{{ $i }}" {{ old('billing_day', 1) == $i ? 'selected' : '' }}>
                                                        {{ $i }} {{ __('st/th of every month') }}
                                                    </option>
                                                @endfor
                                            </select>
                                            <small class="text-muted">{{ __('Day when monthly statement is generated') }}</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group text-center mt-3">
                            <button type="submit" class="btn btn-secondary btn-lg px-5">
                                <i class="fas fa-magic mr-2"></i> {{ __('Create Shop & Initialize Database') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Toggle cPanel DB fields
    $('input[name="db_mode"]').on('change', function() {
        if ($(this).val() === 'cpanel') {
            $('#cpanel_db_fields').slideDown(250);
            $('#custom_db_name').attr('required', true);
        } else {
            $('#cpanel_db_fields').slideUp(250);
            $('#custom_db_name').removeAttr('required');
        }
    });

    // Auto-generate subdomain slug from shop name if subdomain is untouched
    let subdomainManuallyEdited = false;
    $('#subdomain').on('input', function() {
        subdomainManuallyEdited = $(this).val().trim().length > 0;
    });

    $('#shop_name').on('input', function() {
        if (!subdomainManuallyEdited) {
            let slug = $(this).val()
                .toLowerCase()
                .replace(/[^a-z0-9]+/g, '')
                .substring(0, 30);
            $('#subdomain').val(slug);
        }
    });

    // Dynamic Police Station (থানা) loading on District change
    $('#district_id').on('change', function() {
        let districtId = $(this).val();
        let $psDropdown = $('#police_station_id');

        $psDropdown.empty().append('<option value="">{{ __("Loading Police Stations...") }}</option>');

        if (!districtId) {
            $psDropdown.empty().append('<option value="">{{ __("Select District First...") }}</option>');
            return;
        }

        let url = "{{ route('back.tenants.police_stations', ':id') }}".replace(':id', districtId);

        $.getJSON(url, function(data) {
            $psDropdown.empty().append('<option value="">{{ __("Select Police Station (থানা)...") }}</option>');
            if (data.length > 0) {
                $.each(data, function(index, ps) {
                    $psDropdown.append(`<option value="${ps.id}">${ps.name}</option>`);
                });
            } else {
                $psDropdown.append('<option value="">{{ __("No police station found") }}</option>');
            }
        }).fail(function() {
            $psDropdown.empty().append('<option value="">{{ __("Failed to load police stations") }}</option>');
        });
    });

    // If district was already selected (e.g. validation redirect)
    if ($('#district_id').val()) {
        $('#district_id').trigger('change');
    }
});
</script>
@endsection

