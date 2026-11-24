<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

// Handle cache wipe action
if (isset($_GET['action']) && $_GET['action'] === 'clear') {
    $bootstrapCache = __DIR__ . '/../bootstrap/cache';
    $deleted = [];
    if (is_dir($bootstrapCache)) {
        foreach (glob($bootstrapCache . '/*.php') as $f) {
            @unlink($f);
            $deleted[] = basename($f);
        }
    }
    echo "<div style='background:#d4edda;color:#155724;padding:15px;margin-bottom:20px;border-radius:5px;'>";
    echo "<b>Cache files deleted:</b> " . (count($deleted) ? implode(', ', $deleted) : 'None found') . "<br>";
    echo "</div>";
}

echo "<h2>OmniMart Production Diagnostics</h2>";
echo "<p><b>PHP Version:</b> " . phpversion() . "</p>";

// 1. Check vendor/autoload
try {
    require __DIR__ . '/../vendor/autoload.php';
    echo "<p style='color:green;'>&#10004; 1. vendor/autoload.php loaded</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>&#10008; 1. vendor/autoload.php failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// 2. Check bootstrap/app.php
try {
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    echo "<p style='color:green;'>&#10004; 2. bootstrap/app.php loaded</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>&#10008; 2. bootstrap/app.php failed: " . htmlspecialchars($e->getMessage()) . " in " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
    exit;
}

// 3. Check Kernel
try {
    $kernel = $app->make(\Illuminate\Contracts\Http\Kernel::class);
    echo "<p style='color:green;'>&#10004; 3. Http Kernel instantiated</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>&#10008; 3. Kernel instantiation failed: " . htmlspecialchars($e->getMessage()) . "</p>";
    exit;
}

// 4. Check Kernel Bootstrapping
try {
    $request = \Illuminate\Http\Request::capture();
    $app->instance('request', $request);
    $kernel->bootstrap();
    echo "<p style='color:green;'>&#10004; 4. Kernel bootstrapped successfully</p>";
} catch (\Throwable $e) {
    echo "<p style='color:red;'>&#10008; 4. Kernel bootstrap failed: <b>" . htmlspecialchars($e->getMessage()) . "</b> in " . $e->getFile() . ":" . $e->getLine() . "</p>";
    echo "<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre>";
}

// 5. Check Tenancy classes
$classes = [
    'Stancl\Tenancy\Database\Models\Tenant',
    'Stancl\JobPipeline\JobPipeline',
    'Stancl\VirtualColumn\VirtualColumn',
    'App\Models\Tenant',
];
echo "<h4>Tenancy Classes Check:</h4><ul>";
foreach ($classes as $cls) {
    $found = class_exists($cls);
    $color = $found ? 'green' : 'red';
    $mark = $found ? '&#10004;' : '&#10008;';
    echo "<li style='color:{$color}'>{$mark} {$cls} " . ($found ? 'Loaded' : 'NOT FOUND') . "</li>";
}
echo "</ul>";

// 6. Action buttons
echo "<p><a href='?action=clear' style='display:inline-block;padding:10px 15px;background:#dc3545;color:#fff;text-decoration:none;border-radius:4px;font-weight:bold;'>Wipe bootstrap/cache/*.php files</a></p>";

// 7. Last log lines
$logFile = __DIR__ . '/../storage/logs/laravel.log';
if (file_exists($logFile)) {
    $lines = file($logFile);
    $lastLines = array_slice($lines, -60);
    echo "<h3>Latest 60 lines of storage/logs/laravel.log:</h3>";
    echo "<pre style='background:#222;color:#eee;padding:15px;border-radius:5px;max-height:400px;overflow:auto;font-size:12px;'>" . htmlspecialchars(implode('', $lastLines)) . "</pre>";
} else {
    echo "<p>No storage/logs/laravel.log file found.</p>";
}
