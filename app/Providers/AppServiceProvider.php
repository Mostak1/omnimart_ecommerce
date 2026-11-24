<?php

namespace App\Providers;

require_once app_path('Helpers/TenancyStub.php');

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
            $centralConn = config('tenancy.database.central_connection', 'mysql');
            $setting = Setting::find(1);
            $settings->with('setting', $setting);
            $settings->with('site_visibility', VisibilityHelper::map($setting));
            $settings->with('extra_settings', ExtraSetting::find(1));
            $settings->with('menus', DB::connection($centralConn)->table('menus')->find(1));

            if (!session()->has('popup')) {
                view()->share('visit', 1);
            }
            session()->put('popup', 1);
        });
    }

    public function register()
    {
        // Tenancy fallback PSR-4 autoloader for environments without terminal
        spl_autoload_register(function ($class) {
            $prefixes = [
                'Stancl\\Tenancy\\' => base_path('vendor/stancl/tenancy/src/'),
                'Stancl\\JobPipeline\\' => base_path('vendor/stancl/jobpipeline/src/'),
                'Stancl\\VirtualColumn\\' => base_path('vendor/stancl/virtualcolumn/src/'),
            ];

            foreach ($prefixes as $prefix => $baseDir) {
                if (str_starts_with($class, $prefix)) {
                    $relativeClass = substr($class, strlen($prefix));
                    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';
                    if (file_exists($file)) {
                        require_once $file;
                        return true;
                    }
                }
            }
            return false;
        });
    }
}
