<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class TenantCreateDemoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenant:create-demo
                            {subdomain=bhaibhai : The slug or subdomain for the demo tenant}
                            {--shop-name=Bhai Bhai Fashion : The shop name}
                            {--owner=Mohammad Ali : The owner name}
                            {--phone=01711000000 : Contact phone number}
                            {--shop-type=fashion : Type/category of the shop}
                            {--force : Force delete previous tenant and database if exists}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a demo tenant, auto-provision its isolated MySQL database, and execute tenant migrations.';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $subdomain = strtolower(trim((string) $this->argument('subdomain')));
        $shopName = (string) $this->option('shop-name');
        $owner = (string) $this->option('owner');
        $phone = (string) $this->option('phone');
        $shopType = (string) $this->option('shop-type');

        $this->info("=== Initiating Demo Tenant Provisioning ===");
        $this->line("Tenant ID / Subdomain: <comment>{$subdomain}</comment>");

        $existingTenant = Tenant::find($subdomain);

        if ($existingTenant) {
            if ($this->option('force')) {
                $this->warn("Existing tenant found. Deleting previous tenant and database (--force)...");
                $existingTenant->delete();
            } else {
                $this->error("Tenant with ID [{$subdomain}] already exists!");
                $this->line("Use <comment>--force</comment> to recreate or choose a different subdomain.");
                return self::FAILURE;
            }
        }

        $this->info("1. Registering Tenant in central database...");

        /** @var Tenant $tenant */
        $tenant = Tenant::create([
            'id' => $subdomain,
            'shop_name' => $shopName,
            'owner_name' => $owner,
            'phone' => $phone,
            'address' => 'Mirpur-10, Dhaka, Bangladesh',
            'latitude' => 23.8070,
            'longitude' => 90.3686,
            'shop_type' => $shopType,
            'status' => 'active',
        ]);

        $this->info("2. Creating subdomain mapping...");
        $tenant->domains()->create([
            'domain' => $subdomain,
        ]);

        $dbName = $tenant->database()->getName();
        $this->info("3. Provisioned MySQL Tenant Database: <comment>{$dbName}</comment>");

        // Verify Database existence in MySQL
        $dbExists = DB::select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$dbName]);

        if (empty($dbExists)) {
            $this->error("Database [{$dbName}] was not found in MySQL!");
            return self::FAILURE;
        }

        $this->info("   ✔ Verified database [{$dbName}] exists in MySQL server.");

        // Check tables in the tenant database
        $tables = [];
        $tenant->run(function () use (&$tables, $dbName) {
            $tableObjects = DB::select("SHOW TABLES");
            $keyName = "Tables_in_{$dbName}";
            foreach ($tableObjects as $obj) {
                $vars = get_object_vars($obj);
                $tables[] = reset($vars);
            }
        });

        $this->info("4. Tenant Migrations executed. Tables found in [{$dbName}]:");
        foreach ($tables as $table) {
            $this->line("   - {$table}");
        }

        $this->newLine();
        $this->info("=== Tenant Provisioning Completed Successfully! ===");
        $this->table(
            ['Property', 'Value'],
            [
                ['Tenant ID', $tenant->id],
                ['Shop Name', $tenant->shop_name],
                ['Owner Name', $tenant->owner_name],
                ['Phone', $tenant->phone],
                ['Subdomain', "{$subdomain}.localhost"],
                ['Database Name', $dbName],
                ['Status', $tenant->status],
            ]
        );

        $this->comment("You can verify this tenant by testing:");
        $this->line("curl.exe -H \"Host: {$subdomain}.localhost:8000\" http://127.0.0.1:8000/tenant-test");

        return self::SUCCESS;
    }
}

