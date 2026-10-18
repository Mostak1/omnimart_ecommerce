<?php

namespace App\Tenancy;

use App\Tenancy\Exceptions\TenantDatabaseException;
use Illuminate\Database\Connection;
use Illuminate\Support\Facades\DB;
use Throwable;

class TenantDatabaseManager
{
    /**
     * Get the tenant connection name.
     */
    public function tenantConnectionName(): string
    {
        return config('tenancy.tenant_connection_name', 'tenant');
    }

    /**
     * Get the central connection name.
     */
    public function centralConnectionName(): string
    {
        return config('tenancy.central_connection', 'central');
    }

    /**
     * Build and register the database connection configuration for the given tenant.
     */
    public function configureTenantConnection(Tenant $tenant): void
    {
        $connectionName = $this->tenantConnectionName();
        $template = config('tenancy.connection_template', env('DB_CONNECTION', 'mysql'));
        $baseConfig = config("database.connections.{$template}", []);

        // Merge tenant database details over the template configuration
        $tenantConfig = array_merge($baseConfig, [
            'database' => $tenant->database,
        ], $tenant->connectionConfig);

        config(["database.connections.{$connectionName}" => $tenantConfig]);
    }

    /**
     * Connect to the tenant database and set it as the default application connection.
     */
    public function connect(Tenant $tenant): Connection
    {
        $connectionName = $this->tenantConnectionName();

        // 1. Set the dynamic connection config
        $this->configureTenantConnection($tenant);

        // 2. Purge any stale connection for this connection name
        DB::purge($connectionName);

        // 3. Purge current default if different to prevent stale handles
        $currentDefault = DB::getDefaultConnection();
        if ($currentDefault !== $connectionName) {
            DB::purge($currentDefault);
        }

        // 4. Set tenant connection as the default application database connection
        DB::setDefaultConnection($connectionName);

        // 5. Connect and return the active connection
        try {
            return DB::reconnect($connectionName);
        } catch (Throwable $e) {
            throw new TenantDatabaseException(
                "Failed connecting to tenant database [{$tenant->database}] for tenant [{$tenant->id}]: " . $e->getMessage(),
                500,
                $e
            );
        }
    }

    /**
     * Disconnect tenant database and restore the central/system connection as default.
     */
    public function disconnect(): void
    {
        $connectionName = $this->tenantConnectionName();
        $centralConnection = $this->centralConnectionName();

        // 1. Purge tenant connection
        DB::purge($connectionName);

        // 2. Clear tenant connection configuration
        config(["database.connections.{$connectionName}.database" => null]);

        // 3. Restore central connection as default
        if (config()->has("database.connections.{$centralConnection}")) {
            DB::setDefaultConnection($centralConnection);
        } else {
            DB::setDefaultConnection(config('database.default', 'mysql'));
        }
    }

    /**
     * Get the explicit central database connection.
     */
    public function centralConnection(): Connection
    {
        return DB::connection($this->centralConnectionName());
    }

    /**
     * Test connectivity to a tenant's database without altering the global application state.
     */
    public function testConnection(Tenant $tenant): bool
    {
        $tempConnectionName = 'tenant_test_' . $tenant->id;
        $template = config('tenancy.connection_template', env('DB_CONNECTION', 'mysql'));
        $baseConfig = config("database.connections.{$template}", []);

        $options = $baseConfig['options'] ?? [];
        if (defined('PDO::ATTR_TIMEOUT')) {
            $options[\PDO::ATTR_TIMEOUT] = 1;
        }

        $tenantConfig = array_merge($baseConfig, [
            'database' => $tenant->database,
            'options' => $options,
        ], $tenant->connectionConfig);

        // Instant network check if port is unreachable
        $driver = $tenantConfig['driver'] ?? 'mysql';
        if ($driver === 'mysql' && !empty($tenantConfig['host']) && !empty($tenantConfig['port'])) {
            $socket = @fsockopen($tenantConfig['host'], (int) $tenantConfig['port'], $errno, $errstr, 0.3);
            if (!$socket) {
                return false;
            }
            fclose($socket);
        }

        config(["database.connections.{$tempConnectionName}" => $tenantConfig]);

        try {
            DB::connection($tempConnectionName)->getPdo();
            DB::purge($tempConnectionName);
            config(["database.connections.{$tempConnectionName}" => null]);
            return true;
        } catch (Throwable) {
            DB::purge($tempConnectionName);
            config(["database.connections.{$tempConnectionName}" => null]);
            return false;
        }
    }
}
