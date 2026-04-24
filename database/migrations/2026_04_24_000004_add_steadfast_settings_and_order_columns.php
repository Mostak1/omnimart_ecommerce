<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'steadfast_api_key')) {
                $table->string('steadfast_api_key')->nullable()->after('page_section_visibility');
            }
            if (! Schema::hasColumn('settings', 'steadfast_secret_key')) {
                $table->string('steadfast_secret_key')->nullable()->after('steadfast_api_key');
            }
            if (! Schema::hasColumn('settings', 'steadfast_base_url')) {
                $table->string('steadfast_base_url')->nullable()->after('steadfast_secret_key');
            }
            if (! Schema::hasColumn('settings', 'steadfast_webhook_token')) {
                $table->string('steadfast_webhook_token')->nullable()->after('steadfast_base_url');
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            if (! Schema::hasColumn('orders', 'steadfast_consignment_id')) {
                $table->string('steadfast_consignment_id')->nullable()->after('transaction_number');
            }
            if (! Schema::hasColumn('orders', 'steadfast_delivery_status')) {
                $table->string('steadfast_delivery_status')->nullable()->after('steadfast_consignment_id');
            }
            if (! Schema::hasColumn('orders', 'steadfast_last_tracking_response')) {
                $table->longText('steadfast_last_tracking_response')->nullable()->after('steadfast_delivery_status');
            }
            if (! Schema::hasColumn('orders', 'steadfast_order_created_at')) {
                $table->timestamp('steadfast_order_created_at')->nullable()->after('steadfast_last_tracking_response');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            foreach (['steadfast_api_key', 'steadfast_secret_key', 'steadfast_base_url', 'steadfast_webhook_token'] as $column) {
                if (Schema::hasColumn('settings', $column)) {
                    $table->dropColumn($column);
                }
            }
        });

        Schema::table('orders', function (Blueprint $table) {
            foreach (['steadfast_consignment_id', 'steadfast_delivery_status', 'steadfast_last_tracking_response', 'steadfast_order_created_at'] as $column) {
                if (Schema::hasColumn('orders', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
