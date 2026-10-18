<?php

namespace App\Tenancy;

class TenantResolver
{
    /**
     * Normalize host string: lowercase, trim, remove port, remove trailing dot.
     */
    public function normalizeHost(string $host): string
    {
        // Strip port if present
        if (str_contains($host, ':')) {
            $host = explode(':', $host)[0];
        }

        $host = strtolower(trim($host));

        // Strip trailing FQDN dot
        return rtrim($host, '.');
    }

    /**
     * Check if normalized host is designated as a central domain.
     */
    public function isCentralDomain(string $host): bool
    {
        $normalized = $this->normalizeHost($host);
        $centralDomains = (array) config('tenancy.central_domains', []);

        foreach ($centralDomains as $centralDomain) {
            $centralNormalized = $this->normalizeHost($centralDomain);

            if ($normalized === $centralNormalized) {
                return true;
            }

            // Check wildcard central domain like *.admin.example.com
            if (str_starts_with($centralNormalized, '*.')) {
                $base = substr($centralNormalized, 2);
                if (str_ends_with($normalized, '.' . $base) || $normalized === $base) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Resolve a Tenant from an incoming host/domain.
     */
    public function resolveFromHost(string $host): ?Tenant
    {
        $normalized = $this->normalizeHost($host);
        $domainConfigs = (array) config('tenancy.domains', []);

        // 1. Direct exact match
        if (isset($domainConfigs[$normalized])) {
            return Tenant::fromConfig($normalized, $domainConfigs[$normalized]);
        }

        // 2. Check without 'www.' prefix if present
        if (str_starts_with($normalized, 'www.')) {
            $withoutWww = substr($normalized, 4);
            if (isset($domainConfigs[$withoutWww])) {
                return Tenant::fromConfig($withoutWww, $domainConfigs[$withoutWww]);
            }
        } else {
            // 3. Check with 'www.' prefix if config has it
            $withWww = 'www.' . $normalized;
            if (isset($domainConfigs[$withWww])) {
                return Tenant::fromConfig($withWww, $domainConfigs[$withWww]);
            }
        }

        // 4. Check for wildcard patterns like *.example.com or *.test
        foreach ($domainConfigs as $pattern => $config) {
            if (str_starts_with($pattern, '*.')) {
                $basePattern = substr($pattern, 2);
                if (str_ends_with($normalized, '.' . $basePattern)) {
                    $subdomain = substr($normalized, 0, strlen($normalized) - strlen($basePattern) - 1);
                    return Tenant::fromConfig($normalized, $config, defaultTenantId: $subdomain);
                }
            }
        }

        // 5. Check central database tables if configured and available
        return $this->resolveFromCentralDatabase($normalized);
    }

    /**
     * Resolve tenant from central system database if tables exist.
     */
    protected function resolveFromCentralDatabase(string $normalizedHost): ?Tenant
    {
        try {
            $central = config('tenancy.central_connection', 'central');
            if (!\Illuminate\Support\Facades\Schema::connection($central)->hasTable('domains')) {
                return null;
            }

            $domainRecord = \Illuminate\Support\Facades\DB::connection($central)
                ->table('domains')
                ->where('domain', $normalizedHost)
                ->first();

            if (!$domainRecord) {
                return null;
            }

            $tenantRecord = \Illuminate\Support\Facades\DB::connection($central)
                ->table('tenants')
                ->where('id', $domainRecord->tenant_id)
                ->first();

            if (!$tenantRecord) {
                return null;
            }

            return new Tenant(
                id: $tenantRecord->id,
                domain: $domainRecord->domain,
                database: $tenantRecord->database,
                name: $tenantRecord->name,
                connectionConfig: json_decode($tenantRecord->db_config ?? '[]', true) ?: [],
                enabled: (bool) $tenantRecord->enabled,
                meta: json_decode($tenantRecord->meta ?? '[]', true) ?: []
            );
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * Resolve a Tenant by tenant ID (used for queue workers, CLI commands, etc.).
     */
    public function resolveById(string $id): ?Tenant
    {
        $id = strtolower(trim($id));
        $domainConfigs = (array) config('tenancy.domains', []);

        foreach ($domainConfigs as $domain => $config) {
            $tenantId = $config['tenant'] ?? explode('.', $domain)[0];
            if (strtolower($tenantId) === $id) {
                return Tenant::fromConfig($domain, $config, defaultTenantId: $tenantId);
            }
        }

        return null;
    }

    /**
     * Get all uniquely registered tenants.
     *
     * @return array<string, Tenant> Keyed by tenant ID
     */
    public function getAllTenants(): array
    {
        $domainConfigs = (array) config('tenancy.domains', []);
        $tenants = [];

        foreach ($domainConfigs as $domain => $config) {
            $tenant = Tenant::fromConfig($domain, $config);
            if (!isset($tenants[$tenant->id])) {
                $tenants[$tenant->id] = $tenant;
            }
        }

        return $tenants;
    }
}
