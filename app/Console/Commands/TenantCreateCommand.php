<?php

namespace App\Console\Commands;

use App\Tenancy\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantCreateCommand extends Command
{
    protected $signature = 'tenant:create 
                            {tenant : Unique identifier for the tenant (e.g. store3)}
                            {domain : Primary domain or subdomain (e.g. store3.example.com)}
                            {--database= : Database name (defaults to omnimart_{tenant})}
                            {--name= : Friendly store name}
                            {--migrate : Automatically run tenant migrations after creation}
                            {--seed : Seed initial snapshot and data}';

    protected $description = 'Provision a new tenant database and initialize schema';

    public function handle(TenantManager $manager): int
    {
        $id = strtolower(trim($this->argument('tenant')));
        $domain = strtolower(trim($this->argument('domain')));
        $database = $this->option('database') ?: 'omnimart_' . $id;
        $name = $this->option('name') ?: ucfirst($id);

        $this->info("Provisioning Tenant: [{$id}]");
        $this->line("Domain:   {$domain}");
        $this->line("Database: {$database}");
        $this->line("Name:     {$name}");

        // 1. Attempt database creation using central/system connection
        try {
            $centralConnection = config('tenancy.central_connection', 'central');
            $driver = config("database.connections.{$centralConnection}.driver", 'mysql');

            if ($driver === 'mysql') {
                $this->line("Creating MySQL database [{$database}] if it doesn't already exist...");
                DB::connection($centralConnection)->statement("CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                $this->info("Database [{$database}] created / confirmed.");
            } elseif ($driver === 'pgsql') {
                $this->line("Creating PostgreSQL database [{$database}]...");
                DB::connection($centralConnection)->statement("CREATE DATABASE \"{$database}\"");
                $this->info("Database [{$database}] created.");
            } else {
                $this->line("Using driver [{$driver}]. Skipping automatic database create statement.");
            }
        } catch (Throwable $e) {
            $this->warn("Could not execute CREATE DATABASE statement (you may need manual database creation or higher DB privileges): " . $e->getMessage());
        }

        // 2. Build tenant instance
        $tenant = new Tenant(
            id: $id,
            domain: $domain,
            database: $database,
            name: $name,
            enabled: true
        );

        // 3. Run tenant migrations if requested
        if ($this->option('migrate')) {
            $this->line("Running tenant migrations...");
            try {
                $manager->runAsTenant($tenant, function () {
                    $path = config('tenancy.tenant_migrations_path', database_path('tenant-migrations'));
                    $relative = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $path);

                    Artisan::call('migrate', [
                        '--database' => 'tenant',
                        '--path' => $relative,
                        '--force' => true,
                    ], $this->getOutput());

                    if ($this->option('seed')) {
                        $this->line("Seeding tenant database...");
                        Artisan::call('db:seed', [
                            '--database' => 'tenant',
                            '--force' => true,
                        ], $this->getOutput());
                    }
                });
                $this->info("Tenant migrations executed successfully.");
            } catch (Throwable $e) {
                $this->error("Failed to run migrations for tenant [{$id}]: " . $e->getMessage());
            }
        }

        $this->line("--------------------------------------------------");
        $this->info("Tenant provisioning completed.");
        $this->line("Ensure the following entry is in config/tenancy.php 'domains' array:");
        $this->line(<<<PHP

        '{$domain}' => [
            'tenant' => '{$id}',
            'name' => '{$name}',
            'database' => env('TENANT_DB_{$id}_DATABASE', '{$database}'),
        ],
PHP);
        $this->line("--------------------------------------------------");

        return 0;
    }
}
