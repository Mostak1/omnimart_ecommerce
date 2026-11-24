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
use Stancl\Tenancy\Events\DatabaseDeleted;
use Stancl\Tenancy\Events\DeletingDatabase;
use Throwable;

class SafeDeleteTenantDatabase implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** @var TenantWithDatabase */
    protected $tenant;

    public function __construct(TenantWithDatabase $tenant)
    {
        $this->tenant = $tenant;
    }

    public function handle(): bool
    {
        // If delete_database is set to false (e.g. keep cPanel database safe)
        if ($this->tenant->getInternal('delete_database') === false) {
            Log::info("Tenant [{$this->tenant->getTenantKey()}]: delete_database is false. Preserving database.");
            return true;
        }

        try {
            $dbName = $this->tenant->database()->getName();
            $manager = $this->tenant->database()->manager();

            if ($manager->databaseExists($dbName)) {
                event(new DeletingDatabase($this->tenant));
                $manager->deleteDatabase($this->tenant);
                event(new DatabaseDeleted($this->tenant));
            }
        } catch (Throwable $e) {
            Log::warning("Could not drop database for tenant [{$this->tenant->getTenantKey()}]: " . $e->getMessage());
        }

        return true;
    }
}
