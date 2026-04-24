<?php

namespace Database\Seeders;

use App\Models\EmailTemplate;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->seedInitialSnapshot();
        $this->call(DistrictSeeder::class);

        if (EmailTemplate::where('type', 'New Order Admin')->count() == 0) {
            $emailTemplete = new EmailTemplate();
            $emailTemplete->type = 'New Order Admin';
            $emailTemplete->subject = 'New Order';
            $emailTemplete->body = '<p>You Got a order, Transaction number {transaction_number}</p>';
            $emailTemplete->save();
        }
    }

    protected function seedInitialSnapshot(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        if ($this->hasAnyTables()) {
            $this->wipeCurrentDatabase();
        }

        $snapshotPath = public_path('installer/database.sql');

        if (! is_file($snapshotPath)) {
            throw new RuntimeException("Initial database snapshot not found at [{$snapshotPath}].");
        }

        DB::unprepared(file_get_contents($snapshotPath));
    }

    protected function hasAnyTables(): bool
    {
        return count($this->listTables()) > 0;
    }

    protected function wipeCurrentDatabase(): void
    {
        $driver = DB::getDriverName();
        $tables = $this->listTables();

        if ($driver !== 'mysql') {
            throw new RuntimeException(
                'Automatic recovery from a partially migrated database is only implemented for MySQL.'
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        foreach ($tables as $table) {
            DB::statement("DROP TABLE IF EXISTS `{$table}`");
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    protected function listTables(): array
    {
        $database = DB::getDatabaseName();
        $tables = DB::select('SHOW TABLES');
        $key = 'Tables_in_' . $database;

        return array_map(
            static fn ($table) => $table->$key,
            $tables
        );
    }
}
