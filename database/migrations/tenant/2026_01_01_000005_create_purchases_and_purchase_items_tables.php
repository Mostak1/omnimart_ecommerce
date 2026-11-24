<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations on tenant database.
     */
    public function up(): void
    {
        // 1. Purchases Table (পাইকারি বা ভেন্ডর ক্রয়ের হিসাব)
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique(); // ভেন্ডর চালানের মেমো বা ইনভয়েস নম্বর
            $table->string('supplier_name')->nullable();
            $table->string('supplier_phone')->nullable()->index();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->enum('payment_status', ['paid', 'partial', 'due'])->default('paid');
            $table->date('purchase_date')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('tenant_users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Purchase Items Table (ক্রয়কৃত প্রতিটি পণ্যের আইটেম)
        Schema::create('purchase_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('purchase_id')->constrained('purchases')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name')->nullable();
            $table->decimal('unit_cost', 12, 2)->default(0.00);
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('total_cost', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations on tenant database.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchase_items');
        Schema::dropIfExists('purchases');
    }
};

