<?php

declare(strict_types=1);

/**
 * Fallback stubs for Stancl Tenancy.
 * Prevents fatal 500 errors on production servers where stancl/tenancy
 * has not yet been extracted into the vendor/ directory.
 */
if (!class_exists('Stancl\Tenancy\Database\Models\Tenant', false)) {
    if (!class_exists('Stancl\Tenancy\Database\Models\Tenant')) {
        eval('
            namespace Stancl\Tenancy\Database\Models;
            class Tenant extends \Illuminate\Database\Eloquent\Model {
                public function database() {
                    return new class {
                        public function getName() { return "central"; }
                    };
                }
                public function domains() {
                    return $this->hasMany(\App\Models\Domain::class ?? \Illuminate\Database\Eloquent\Model::class, "tenant_id");
                }
                public function run(callable $callback) {
                    return $callback();
                }
                public static function getCustomColumns(): array { return []; }
            }
        ');
    }
}

if (!interface_exists('Stancl\Tenancy\Contracts\TenantWithDatabase', false)) {
    eval('
        namespace Stancl\Tenancy\Contracts;
        interface TenantWithDatabase {}
    ');
}

if (!trait_exists('Stancl\Tenancy\Database\Concerns\HasDatabase', false)) {
    eval('
        namespace Stancl\Tenancy\Database\Concerns;
        trait HasDatabase {}
    ');
}

if (!trait_exists('Stancl\Tenancy\Database\Concerns\HasDomains', false)) {
    eval('
        namespace Stancl\Tenancy\Database\Concerns;
        trait HasDomains {}
    ');
}

if (!trait_exists('Stancl\Tenancy\Database\Concerns\CentralConnection', false)) {
    eval('
        namespace Stancl\Tenancy\Database\Concerns;
        trait CentralConnection {}
    ');
}
