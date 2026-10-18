<?php

namespace App\Console\Commands;

use App\Tenancy\TenantDatabaseManager;
use App\Tenancy\TenantResolver;
use Illuminate\Console\Command;

class TenantListCommand extends Command
{
    protected $signature = 'tenant:list';
    protected $description = 'List all configured tenants and test database connectivity';

    public function handle(TenantResolver $resolver, TenantDatabaseManager $dbManager): int
    {
        $tenants = $resolver->getAllTenants();

        if (empty($tenants)) {
            $this->warn('No tenants currently configured in config/tenancy.php.');
            return 0;
        }

        $rows = [];
        foreach ($tenants as $tenant) {
            $isConnected = $dbManager->testConnection($tenant);

            $rows[] = [
                'ID' => $tenant->id,
                'Name' => $tenant->name,
                'Domain' => $tenant->domain,
                'Database' => $tenant->database,
                'Enabled' => $tenant->enabled ? '<info>Yes</info>' : '<comment>No</comment>',
                'Connection' => $isConnected ? '<info>OK</info>' : '<error>Failed</error>',
            ];
        }

        $this->table(['ID', 'Name', 'Domain', 'Database', 'Enabled', 'Connection'], $rows);

        return 0;
    }
}
