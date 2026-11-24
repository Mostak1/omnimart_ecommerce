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
        // 1. Sales Table (POS বিক্রয় ও ক্যাশ মেমোর হিসাব)
        Schema::create('sales', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_no')->unique();
            $table->string('customer_name')->nullable();
            $table->string('customer_phone')->nullable()->index();
            $table->decimal('total_amount', 12, 2)->default(0.00);
            $table->decimal('discount_amount', 12, 2)->default(0.00);
            $table->decimal('tax_amount', 12, 2)->default(0.00);
            $table->decimal('paid_amount', 12, 2)->default(0.00);
            $table->decimal('due_amount', 12, 2)->default(0.00);
            $table->string('payment_method')->default('cash'); // cash, card, bkash, nagad, rocket
            $table->enum('payment_status', ['paid', 'partial', 'due'])->default('paid');
            $table->foreignId('created_by')->nullable()->constrained('tenant_users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        // 2. Sale Items Table (মেমোতে থাকা প্রতিটি পণ্যের বিস্তারিত)
        Schema::create('sale_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnDelete();
            $table->foreignId('product_id')->nullable()->constrained('products')->nullOnDelete();
            $table->string('product_name');
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('quantity', 12, 2)->default(1.00);
            $table->decimal('subtotal', 12, 2)->default(0.00);
            $table->decimal('discount', 12, 2)->default(0.00);
            $table->decimal('total', 12, 2)->default(0.00);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations on tenant database.
     */
    public function down(): void
    {
        Schema::dropIfExists('sale_items');
        Schema::dropIfExists('sales');
    }
};

