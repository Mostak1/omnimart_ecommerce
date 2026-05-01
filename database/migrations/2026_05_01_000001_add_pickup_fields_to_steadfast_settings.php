<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'steadfast_pickup_address')) {
                $table->text('steadfast_pickup_address')->nullable()->after('steadfast_base_url');
            }

            if (! Schema::hasColumn('settings', 'steadfast_pickup_phone')) {
                $table->string('steadfast_pickup_phone')->nullable()->after('steadfast_pickup_address');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['steadfast_pickup_address', 'steadfast_pickup_phone'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
