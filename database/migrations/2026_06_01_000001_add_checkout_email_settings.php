<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'is_checkout_email')) {
                $table->tinyInteger('is_checkout_email')->default(1)->after('is_checkout_police_station_required');
            }

            if (! Schema::hasColumn('settings', 'is_checkout_email_required')) {
                $table->tinyInteger('is_checkout_email_required')->default(1)->after('is_checkout_email');
            }
        });
    }

    public function down()
    {
        Schema::table('settings', function (Blueprint $table) {
            $columns = [
                'is_checkout_email',
                'is_checkout_email_required',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
