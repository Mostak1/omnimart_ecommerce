<?php

namespace App\Console\Commands;

use App\Tenancy\TenantManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class TenantRunCommand extends Command
{
    protected $signature = 'tenant:run {tenant : The tenant ID or domain} {artisan_command : The artisan command to run (e.g. "model:prune" or "route:list")}';
    protected $description = 'Execute an Artisan command scoped strictly to a specific tenant database';

    public function handle(TenantManager $manager): int
    {
        $tenantIdentifier = $this->argument('tenant');
        $artisanCommand = $this->argument('artisan_command');

        $resolver = $manager->getResolver();
        $tenant = $resolver->resolveById($tenantIdentifier) ?? $resolver->resolveFromHost($tenantIdentifier);

        if (!$tenant) {
            $this->error("Tenant [{$tenantIdentifier}] not found in tenancy configuration.");
            return 1;
        }

        $this->info("Bootstrapping tenant [{$tenant->id}] (DB: {$tenant->database})...");

        try {
            return $manager->runAsTenant($tenant, function () use ($artisanCommand) {
                $this->line("Running: php artisan {$artisanCommand}");
                $exitCode = Artisan::call($artisanCommand, [], $this->getOutput());
                return $exitCode;
            });
        } catch (Throwable $e) {
            $this->error("Command execution failed: " . $e->getMessage());
            return 1;
        }
    }
}
