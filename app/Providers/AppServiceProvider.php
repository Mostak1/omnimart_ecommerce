<?php

namespace App\Providers;

use App\Helpers\VisibilityHelper;
use App\Models\ExtraSetting;
use App\Models\Setting;
use Illuminate\{
    Support\ServiceProvider,
    Support\Facades\DB
};
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        Paginator::useBootstrap();
        view()->composer('*', function ($settings) {
            $setting = Setting::find(1);
            $settings->with('setting', $setting);
            $settings->with('site_visibility', VisibilityHelper::map($setting));
            $settings->with('extra_settings', ExtraSetting::find(1));
            $settings->with('menus', DB::table('menus')->find(1));

            if (!session()->has('popup')) {
                view()->share('visit', 1);
            }
            session()->put('popup', 1);
        });
    }

    public function register()
    {
    }
}
