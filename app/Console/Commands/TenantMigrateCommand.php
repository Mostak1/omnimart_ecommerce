<?php

namespace App\Console\Commands;

use App\Tenancy\Tenant;
use App\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate 
                            {tenant? : The optional tenant ID to migrate. If omitted, all tenants are migrated.}
                            {--fresh : Drop all tables and re-run all migrations}
                            {--seed : Run seeders after migration}
                            {--path= : Custom migration path (defaults to config tenancy.tenant_migrations_path)}';

    protected $description = 'Run migrations against one or all tenant databases';

    public function handle(TenantManager $manager): int
    {
        $tenantIdentifier = $this->argument('tenant');
        $resolver = $manager->getResolver();

        if ($tenantIdentifier) {
            $tenant = $resolver->resolveById($tenantIdentifier) ?? $resolver->resolveFromHost($tenantIdentifier);
            if (!$tenant) {
                $this->error("Tenant [{$tenantIdentifier}] not found.");
                return 1;
            }
            $tenants = [$tenant];
        } else {
            $tenants = array_values($resolver->getAllTenants());
            if (empty($tenants)) {
                $this->warn('No tenants found in configuration to migrate.');
                return 0;
            }
        }

        $isFresh = (bool) $this->option('fresh');
        $shouldSeed = (bool) $this->option('seed');
        $customPath = $this->option('path') ?: config('tenancy.tenant_migrations_path', database_path('tenant-migrations'));

        $this->info(sprintf('Migrating %d tenant(s)...', count($tenants)));

        $overallExit = 0;

        foreach ($tenants as $tenant) {
            $this->line("--------------------------------------------------");
            $this->info("Migrating Tenant: [{$tenant->id}] -> Database: [{$tenant->database}]");

            try {
                $manager->runAsTenant($tenant, function () use ($isFresh, $shouldSeed, $customPath) {
                    $relativeMigrationPath = str_replace(base_path() . DIRECTORY_SEPARATOR, '', $customPath);

                    if ($isFresh) {
                        $this->line("Running migrate:fresh on tenant database...");
                        Artisan::call('migrate:fresh', [
                            '--database' => 'tenant',
                            '--path' => $relativeMigrationPath,
                            '--force' => true,
                        ], $this->getOutput());
                    } else {
                        $this->line("Running migrate on tenant database...");
                        Artisan::call('migrate', [
                            '--database' => 'tenant',
                            '--path' => $relativeMigrationPath,
                            '--force' => true,
                        ], $this->getOutput());
                    }

                    if ($shouldSeed) {
                        $this->line("Seeding tenant database...");
                        Artisan::call('db:seed', [
                            '--database' => 'tenant',
                            '--force' => true,
                        ], $this->getOutput());
                    }
                });

                $this->info("Tenant [{$tenant->id}] migrated successfully.");
            } catch (Throwable $e) {
                $this->error("Failed migrating tenant [{$tenant->id}]: " . $e->getMessage());
                $overallExit = 1;
            }
        }

        $this->line("--------------------------------------------------");
        $this->info("Tenants migration process completed.");

        return $overallExit;
    }
}
