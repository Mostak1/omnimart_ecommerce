<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Tenancy Master Switch
    |--------------------------------------------------------------------------
    |
    | When enabled, requests are resolved to a tenant context and database.
    | When disabled (e.g. during certain CLI operations or maintenance),
    | the standard default database configuration is retained.
    |
    */
    'enabled' => env('TENANCY_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Central Connection & Central Domains
    |--------------------------------------------------------------------------
    |
    | Central domains (e.g. system administration, platform landing page)
    | operate on the central database and do NOT switch to a tenant database.
    |
    */
    'central_connection' => env('TENANCY_CENTRAL_CONNECTION', 'central'),

    'central_domains' => array_filter(array_map('trim', explode(',', env('TENANCY_CENTRAL_DOMAINS', 'admin.example.com,central.localhost,main.example.com')))),

    /*
    |--------------------------------------------------------------------------
    | Database Connection Configuration
    |--------------------------------------------------------------------------
    |
    | The default connection template to clone settings from (e.g. 'mysql' or 'sqlite').
    | The tenant connection will be registered dynamically under 'tenant_connection_name'.
    |
    */
    'tenant_connection_name' => 'tenant',
    'connection_template' => env('TENANCY_TEMPLATE_CONNECTION', env('DB_CONNECTION', 'mysql')),

    /*
    |--------------------------------------------------------------------------
    | Domain to Tenant / Database Mapping
    |--------------------------------------------------------------------------
    |
    | Configurable in code. Supports root domains, subdomains, localhost, and custom ports.
    | Hostnames are automatically normalized (lowercase, trim, port removed, www stripped).
    |
    | Example:
    |   'example1.com' => ['database' => 'database_1'],
    |   'store1.test'  => [
    |       'tenant'   => 'store1',
    |       'name'     => 'Store One',
    |       'database' => env('TENANT_DB_1_DATABASE', 'store1_db'),
    |       'host'     => env('TENANT_DB_1_HOST', '127.0.0.1'),
    |       'username' => env('TENANT_DB_1_USERNAME', 'root'),
    |       'password' => env('TENANT_DB_1_PASSWORD', ''),
    |   ],
    |
    */
    'domains' => [

        // Development / local test domains
        'store1.test' => [
            'tenant' => 'store1',
            'name' => 'Store 1',
            'database' => env('TENANT_DB_1_DATABASE', 'omnimart_store1'),
            'host' => env('TENANT_DB_1_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TENANT_DB_1_PORT', env('DB_PORT', '3306')),
            'username' => env('TENANT_DB_1_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('TENANT_DB_1_PASSWORD', env('DB_PASSWORD', '')),
        ],

        'store2.test' => [
            'tenant' => 'store2',
            'name' => 'Store 2',
            'database' => env('TENANT_DB_2_DATABASE', 'omnimart_store2'),
            'host' => env('TENANT_DB_2_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TENANT_DB_2_PORT', env('DB_PORT', '3306')),
            'username' => env('TENANT_DB_2_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('TENANT_DB_2_PASSWORD', env('DB_PASSWORD', '')),
        ],

        'store1.localhost' => [
            'tenant' => 'store1',
            'name' => 'Store 1',
            'database' => env('TENANT_DB_1_DATABASE', 'omnimart_store1'),
            'host' => env('TENANT_DB_1_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TENANT_DB_1_PORT', env('DB_PORT', '3306')),
            'username' => env('TENANT_DB_1_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('TENANT_DB_1_PASSWORD', env('DB_PASSWORD', '')),
        ],

        'store2.localhost' => [
            'tenant' => 'store2',
            'name' => 'Store 2',
            'database' => env('TENANT_DB_2_DATABASE', 'omnimart_store2'),
            'host' => env('TENANT_DB_2_HOST', env('DB_HOST', '127.0.0.1')),
            'port' => env('TENANT_DB_2_PORT', env('DB_PORT', '3306')),
            'username' => env('TENANT_DB_2_USERNAME', env('DB_USERNAME', 'root')),
            'password' => env('TENANT_DB_2_PASSWORD', env('DB_PASSWORD', '')),
        ],

        // Production-style example domains
        'example1.com' => [
            'tenant' => 'example1',
            'name' => 'Example One',
            'database' => env('TENANT_DB_1_DATABASE', 'omnimart_store1'),
        ],

        'example2.com' => [
            'tenant' => 'example2',
            'name' => 'Example Two',
            'database' => env('TENANT_DB_2_DATABASE', 'omnimart_store2'),
        ],

        'shop1.example.com' => [
            'tenant' => 'shop1',
            'name' => 'Shop One',
            'database' => env('TENANT_DB_1_DATABASE', 'omnimart_store1'),
        ],

        'shop2.example.com' => [
            'tenant' => 'shop2',
            'name' => 'Shop Two',
            'database' => env('TENANT_DB_2_DATABASE', 'omnimart_store2'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Context Locking
    |--------------------------------------------------------------------------
    |
    | Once resolved during an HTTP request, the tenant context is locked.
    | Attempting to change the tenant context halfway through throws an exception.
    |
    */
    'lock_context' => true,

    /*
    |--------------------------------------------------------------------------
    | Isolation Settings
    |--------------------------------------------------------------------------
    */
    'cache' => [
        'prefix_by_tenant' => true,
        'isolate_file_store' => true,
    ],

    'session' => [
        'prefix_cookie_by_tenant' => true,
        'isolate_file_store' => true,
    ],

    'storage' => [
        'isolate_public_disk' => true,
        'tenant_folder_prefix' => 'tenants/',
    ],

    'logging' => [
        'add_context' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Tenant Migrations Path
    |--------------------------------------------------------------------------
    */
    'tenant_migrations_path' => database_path('tenant-migrations'),

];
