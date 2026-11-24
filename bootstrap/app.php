<?php

// Autoloader for packages (such as stancl tenancy when composer install is skipped on cPanel)
spl_autoload_register(function ($class) {
    $prefixes = [
        'Stancl\\Tenancy\\' => [
            __DIR__ . '/../packages/stancl/tenancy/src/',
            __DIR__ . '/../vendor/stancl/tenancy/src/',
        ],
        'Stancl\\JobPipeline\\' => [
            __DIR__ . '/../packages/stancl/jobpipeline/src/',
            __DIR__ . '/../vendor/stancl/jobpipeline/src/',
        ],
        'Stancl\\VirtualColumn\\' => [
            __DIR__ . '/../packages/stancl/virtualcolumn/src/',
            __DIR__ . '/../vendor/stancl/virtualcolumn/src/',
        ],
    ];

    foreach ($prefixes as $prefix => $dirs) {
        if (str_starts_with($class, $prefix)) {
            $relativeClass = substr($class, strlen($prefix));
            $file = str_replace('\\', '/', $relativeClass) . '.php';
            foreach ($dirs as $dir) {
                if (file_exists($dir . $file)) {
                    require_once $dir . $file;
                    return;
                }
            }
        }
    }
});

// Laravel version compatibility fallbacks (e.g. ReflectsClosures trait)
if (!trait_exists('Illuminate\Support\Traits\ReflectsClosures', false)) {
    foreach ([
        __DIR__ . '/../packages/framework_patches/Reflection/Traits/ReflectsClosures.php',
        __DIR__ . '/../packages/framework_patches/ReflectsClosures.php',
        __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Support/Traits/ReflectsClosures.php',
        __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Reflection/Traits/ReflectsClosures.php',
    ] as $candidate) {
        if (file_exists($candidate)) {
            require_once $candidate;
            break;
        }
    }
}
if (!class_exists('Illuminate\Support\Reflector', false)) {
    foreach ([
        __DIR__ . '/../packages/framework_patches/Reflection/Reflector.php',
        __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Support/Reflector.php',
        __DIR__ . '/../vendor/laravel/framework/src/Illuminate/Reflection/Reflector.php',
    ] as $candidate) {
        if (file_exists($candidate)) {
            require_once $candidate;
            break;
        }
    }
}

// Load Stancl Tenancy helper functions if available
foreach ([
    __DIR__ . '/../packages/stancl/tenancy/src/helpers.php',
    __DIR__ . '/../vendor/stancl/tenancy/src/helpers.php',
] as $helperPath) {
    if (file_exists($helperPath)) {
        require_once $helperPath;
        break;
    }
}

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    $_ENV['APP_BASE_PATH'] ?? dirname(__DIR__)
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
