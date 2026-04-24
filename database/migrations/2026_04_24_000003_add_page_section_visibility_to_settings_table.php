<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (! Schema::hasColumn('settings', 'page_section_visibility')) {
                $table->longText('page_section_visibility')->nullable()->after('is_mail_verify');
            }
        });
    }

    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'page_section_visibility')) {
                $table->dropColumn('page_section_visibility');
            }
        });
    }
};
