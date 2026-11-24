<?php

declare(strict_types=1);

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\DatabaseManager;
use Stancl\Tenancy\Events\CreatingDatabase;
use Stancl\Tenancy\Events\DatabaseCreated;
use Throwable;

class EnsureTenantDatabaseCreated implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(DatabaseManager $databaseManager): bool
    {
        $dbName = $this->tenant->database()->getName();
        $manager = $this->tenant->database()->manager();

        // 1. If database already exists (e.g. pre-created in cPanel / manual mode)
        try {
            if ($manager->databaseExists($dbName)) {
                Log::info("Tenant [{$this->tenant->getTenantKey()}]: Using pre-existing database [{$dbName}]. Skipping CREATE DATABASE.");
                // Return true so Stancl JobPipeline continues to MigrateDatabase!
                return true;
            }
        } catch (Throwable $e) {
            Log::warning("Tenant [{$this->tenant->getTenantKey()}]: databaseExists check error: " . $e->getMessage());
        }

        // 2. If explicitly instructed not to create database
        if ($this->tenant->getInternal('create_database') === false) {
            Log::info("Tenant [{$this->tenant->getTenantKey()}]: create_database is false. Skipping CREATE DATABASE.");
            return true;
        }

        // 3. Auto mode: dynamically create database
        event(new CreatingDatabase($this->tenant));

        $this->tenant->database()->makeCredentials();
        $manager->createDatabase($this->tenant);

        event(new DatabaseCreated($this->tenant));

        return true;
    }
}
