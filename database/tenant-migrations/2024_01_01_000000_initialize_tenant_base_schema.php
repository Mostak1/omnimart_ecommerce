<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Initializes base tables from snapshot if the database is newly created.
     */
    public function up(): void
    {
        if (Schema::hasTable('settings')) {
            return;
        }

        $snapshotPath = public_path('installer/database.sql');

        if (is_file($snapshotPath) && DB::getDriverName() === 'mysql') {
            DB::unprepared(file_get_contents($snapshotPath));
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Dropping full base schema should be done through tenant drop commands
    }
};
