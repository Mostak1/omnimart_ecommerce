<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run central system migrations.
     * Creates central tables for future dynamic database-driven tenant registration.
     */
    public function up(): void
    {
        $connection = config('tenancy.central_connection', 'central');

        if (!Schema::connection($connection)->hasTable('tenants')) {
            Schema::connection($connection)->create('tenants', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->string('name');
                $table->string('database')->unique();
                $table->boolean('enabled')->default(true);
                $table->json('db_config')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::connection($connection)->hasTable('domains')) {
            Schema::connection($connection)->create('domains', function (Blueprint $table) {
                $table->id();
                $table->string('domain')->unique();
                $table->string('tenant_id');
                $table->boolean('is_primary')->default(false);
                $table->timestamps();

                $table->foreign('tenant_id')->references('id')->on('tenants')->cascadeOnDelete();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('tenancy.central_connection', 'central');
        Schema::connection($connection)->dropIfExists('domains');
        Schema::connection($connection)->dropIfExists('tenants');
    }
};
