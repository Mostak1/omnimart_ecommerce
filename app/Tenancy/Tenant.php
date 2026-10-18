<?php

namespace App\Tenancy;

class Tenant
{
    public string $id;
    public string $domain;
    public string $name;
    public string $database;
    public array $connectionConfig;
    public bool $enabled;
    public array $meta;

    public function __construct(
        string $id,
        string $domain,
        string $database,
        string $name = '',
        array $connectionConfig = [],
        bool $enabled = true,
        array $meta = []
    ) {
        $this->id = $id;
        $this->domain = $domain;
        $this->database = $database;
        $this->name = $name ?: ucfirst($id);
        $this->connectionConfig = $connectionConfig;
        $this->enabled = $enabled;
        $this->meta = $meta;
    }

    public static function fromConfig(string $domain, array $config, string $defaultTenantId = ''): self
    {
        $id = $config['tenant'] ?? ($defaultTenantId ?: explode('.', $domain)[0]);
        $database = $config['database'] ?? '';
        $name = $config['name'] ?? ucfirst($id);
        $enabled = $config['enabled'] ?? true;

        $dbKeys = ['driver', 'host', 'port', 'username', 'password', 'charset', 'collation', 'prefix', 'strict', 'engine', 'options'];
        $connectionConfig = array_filter(
            $config,
            fn($k) => in_array($k, $dbKeys, true),
            ARRAY_FILTER_USE_KEY
        );

        $meta = $config['meta'] ?? [];

        return new self(
            id: $id,
            domain: $domain,
            database: $database,
            name: $name,
            connectionConfig: $connectionConfig,
            enabled: (bool) $enabled,
            meta: $meta
        );
    }

    public function isActive(): bool
    {
        return $this->enabled;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'domain' => $this->domain,
            'name' => $this->name,
            'database' => $this->database,
            'connection' => $this->connectionConfig,
            'enabled' => $this->enabled,
            'meta' => $this->meta,
        ];
    }
}
