<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('shipping_services', function (Blueprint $table) {
            $table->tinyInteger('is_automated')->default(0)->after('is_condition');
            $table->double('dhaka_price')->default(0)->after('is_automated');
            $table->double('outside_dhaka_price')->default(0)->after('dhaka_price');
            $table->double('per_kg_price')->default(0)->after('outside_dhaka_price');
        });

        Schema::table('items', function (Blueprint $table) {
            $table->double('shipping_weight')->default(1)->after('stock');
        });
    }

    public function down(): void
    {
        Schema::table('shipping_services', function (Blueprint $table) {
            $table->dropColumn(['is_automated', 'dhaka_price', 'outside_dhaka_price', 'per_kg_price']);
        });

        Schema::table('items', function (Blueprint $table) {
            $table->dropColumn('shipping_weight');
        });
    }
};
