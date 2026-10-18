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

        'ecom.techseba.com' => [
            'tenant'   => 'techseba',
            'name'     => 'TechSeba Store',
            'database' => 'techseba_ecom',
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
