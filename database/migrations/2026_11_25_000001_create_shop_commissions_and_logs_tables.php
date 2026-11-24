<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations on Central Database.
     */
    public function up(): void
    {
        // 1. Shop Commissions / Wallets Table
        Schema::create('shop_commissions', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id')->unique();
            $table->decimal('commission_rate', 5, 2)->default(10.00); // 10% default
            $table->decimal('current_due', 12, 2)->default(0.00);
            $table->decimal('total_earned_commission', 12, 2)->default(0.00);
            $table->decimal('total_paid_commission', 12, 2)->default(0.00);
            $table->decimal('max_due_limit', 12, 2)->default(1000.00); // Hard credit limit
            $table->decimal('min_due_limit', 12, 2)->default(200.00);  // Min threshold for monthly billing
            $table->unsignedTinyInteger('billing_day')->default(1);     // Day of month (1 to 28)
            $table->enum('marketplace_status', ['active', 'paused_due_limit', 'suspended'])->default('active');
            $table->date('last_billed_at')->nullable();
            $table->timestamp('last_paid_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
        });

        // 2. Shop Commission Transactions & Ledger Logs
        Schema::create('shop_commission_logs', function (Blueprint $table) {
            $table->id();
            $table->string('tenant_id');
            $table->unsignedBigInteger('order_id')->nullable();
            $table->enum('type', [
                'order_commission',   // Commission added to due upon order placement/completion
                'commission_payment', // Shop paid due via bKash/Nagad/Bank
                'adjustment',         // Admin manual credit/debit
                'refund',             // Order cancelled/refunded
            ]);
            $table->decimal('amount', 12, 2);
            $table->decimal('balance_before', 12, 2)->default(0.00);
            $table->decimal('balance_after', 12, 2)->default(0.00);
            $table->string('payment_method')->nullable(); // bkash, nagad, bank, manual_admin, etc.
            $table->string('trx_id')->nullable();
            $table->enum('status', ['completed', 'pending', 'rejected'])->default('completed');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')->references('id')->on('tenants')->onDelete('cascade');
            $table->index(['tenant_id', 'created_at']);
        });

        // 3. Add optional tenant_id to items table so products on Central Marketplace can be associated with specific shops
        if (Schema::hasTable('items') && !Schema::hasColumn('items', 'tenant_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->string('tenant_id')->nullable()->after('id')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('items') && Schema::hasColumn('items', 'tenant_id')) {
            Schema::table('items', function (Blueprint $table) {
                $table->dropColumn('tenant_id');
            });
        }
        Schema::dropIfExists('shop_commission_logs');
        Schema::dropIfExists('shop_commissions');
    }
};
