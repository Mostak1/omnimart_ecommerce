<?php

namespace App\Providers;

use App\Console\Commands\TenantCreateCommand;
use App\Console\Commands\TenantListCommand;
use App\Console\Commands\TenantMigrateCommand;
use App\Console\Commands\TenantRunCommand;
use App\Tenancy\TenantContext;
use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantManager;
use App\Tenancy\TenantResolver;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;

class TenancyServiceProvider extends ServiceProvider
{
    /**
     * Register tenancy services.
     */
    public function register(): void
    {
        // 1. Merge default configuration
        $this->mergeConfigFrom(__DIR__ . '/../../config/tenancy.php', 'tenancy');

        // 2. Register Singletons
        $this->app->singleton(TenantContext::class, function () {
            return new TenantContext();
        });

        $this->app->singleton(TenantResolver::class, function () {
            return new TenantResolver();
        });

        $this->app->singleton(TenantDatabaseManager::class, function () {
            return new TenantDatabaseManager();
        });

        $this->app->singleton(TenantManager::class, function ($app) {
            return new TenantManager(
                $app->make(TenantContext::class),
                $app->make(TenantResolver::class),
                $app->make(TenantDatabaseManager::class)
            );
        });
    }

    /**
     * Bootstrap tenancy services and event listeners.
     */
    public function boot(): void
    {
        // Register console commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                TenantCreateCommand::class,
                TenantMigrateCommand::class,
                TenantRunCommand::class,
                TenantListCommand::class,
            ]);
        }

        // Configure Queue tenancy isolation and context propagation
        $this->registerQueueTenancyHooks();
    }

    /**
     * Ensure queued jobs automatically retain tenant context from dispatch
     * to worker execution, and reset cleanly after each job.
     */
    protected function registerQueueTenancyHooks(): void
    {
        if (!$this->app->bound('queue')) {
            return;
        }

        // 1. Inject tenant_id into queue payload when a job is dispatched
        Queue::createPayloadUsing(function ($connection, $queue, $payload) {
            if ($this->app->bound(TenantContext::class)) {
                $context = $this->app->make(TenantContext::class);
                if ($context->hasTenant()) {
                    return ['tenant_id' => $context->id()];
                }
            }
            return [];
        });

        // 2. Before a worker handles a job: bootstrap the job's tenant context
        Queue::before(function (JobProcessing $event) {
            $payload = $event->job->payload();
            $tenantId = $payload['tenant_id'] ?? null;

            if ($tenantId && $this->app->bound(TenantManager::class)) {
                $manager = $this->app->make(TenantManager::class);
                $manager->bootstrapTenantById($tenantId, lock: false);
            }
        });

        // 3. After worker completes job: reset connection and state to prevent leakage
        Queue::after(function (JobProcessed $event) {
            if ($this->app->bound(TenantManager::class)) {
                $this->app->make(TenantManager::class)->reset();
            }
        });

        // 4. When a job fails: reset connection and state
        Queue::failing(function (JobFailed $event) {
            if ($this->app->bound(TenantManager::class)) {
                $this->app->make(TenantManager::class)->reset();
            }
        });

        // 5. In daemon worker loop: ensure clean state before waiting for new jobs
        Queue::looping(function () {
            if ($this->app->bound(TenantManager::class)) {
                $context = $this->app->make(TenantContext::class);
                if ($context->hasTenant()) {
                    $this->app->make(TenantManager::class)->reset();
                }
            }
        });
    }
}
