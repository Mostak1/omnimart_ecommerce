<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->text('facebook_access_token')->nullable();
            $table->string('facebook_pixel_id')->nullable();
            $table->string('facebook_test_code')->nullable();
            $table->tinyInteger('is_facebook_capi')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn(['facebook_access_token', 'facebook_pixel_id', 'facebook_test_code', 'is_facebook_capi']);
        });
    }
};
