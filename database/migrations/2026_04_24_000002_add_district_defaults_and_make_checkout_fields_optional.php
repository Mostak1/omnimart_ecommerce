<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('shipping_services', 'default_base_shipping_charge')) {
            Schema::table('shipping_services', function (Blueprint $table) {
                $table->decimal('default_base_shipping_charge', 10, 2)->default(120)->after('per_kg_price');
            });
        }

        if (! Schema::hasColumn('shipping_services', 'default_per_kg_extra_charge')) {
            Schema::table('shipping_services', function (Blueprint $table) {
                $table->decimal('default_per_kg_extra_charge', 10, 2)->default(30)->after('default_base_shipping_charge');
            });
        }

        if (! Schema::hasColumn('items', 'shipping_weight')) {
            Schema::table('items', function (Blueprint $table) {
                $table->decimal('shipping_weight', 10, 2)->nullable()->after('stock');
            });
        }

        if (Schema::hasColumn('items', 'shipping_weight')) {
            DB::statement('ALTER TABLE items MODIFY shipping_weight DECIMAL(10,2) NULL');
        }

        if (Schema::hasColumn('orders', 'bill_email')) {
            DB::statement('ALTER TABLE orders MODIFY bill_email VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('shipping_services', 'default_per_kg_extra_charge')) {
            Schema::table('shipping_services', function (Blueprint $table) {
                $table->dropColumn('default_per_kg_extra_charge');
            });
        }

        if (Schema::hasColumn('shipping_services', 'default_base_shipping_charge')) {
            Schema::table('shipping_services', function (Blueprint $table) {
                $table->dropColumn('default_base_shipping_charge');
            });
        }

        if (Schema::hasColumn('items', 'shipping_weight')) {
            DB::statement('ALTER TABLE items MODIFY shipping_weight DECIMAL(10,2) NOT NULL DEFAULT 1');
        }

        if (Schema::hasColumn('orders', 'bill_email')) {
            DB::statement('ALTER TABLE orders MODIFY bill_email VARCHAR(255) NOT NULL');
        }
    }
};
