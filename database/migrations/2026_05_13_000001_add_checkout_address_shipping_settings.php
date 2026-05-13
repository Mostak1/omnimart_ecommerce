<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'is_checkout_district')) {
                $table->tinyInteger('is_checkout_district')->default(1)->after('is_single_checkout');
            }

            if (! Schema::hasColumn('settings', 'is_checkout_district_required')) {
                $table->tinyInteger('is_checkout_district_required')->default(1)->after('is_checkout_district');
            }

            if (! Schema::hasColumn('settings', 'is_checkout_police_station')) {
                $table->tinyInteger('is_checkout_police_station')->default(1)->after('is_checkout_district_required');
            }

            if (! Schema::hasColumn('settings', 'is_checkout_police_station_required')) {
                $table->tinyInteger('is_checkout_police_station_required')->default(1)->after('is_checkout_police_station');
            }

            if (! Schema::hasColumn('settings', 'checkout_shipping_charge_source')) {
                $table->string('checkout_shipping_charge_source', 20)->default('district')->after('is_checkout_police_station_required');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'is_checkout_district',
                'is_checkout_district_required',
                'is_checkout_police_station',
                'is_checkout_police_station_required',
                'checkout_shipping_charge_source',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
