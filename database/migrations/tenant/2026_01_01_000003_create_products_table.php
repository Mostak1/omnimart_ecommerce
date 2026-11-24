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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('global_item_id')->nullable()->index(); // Reference to central items table
            $table->string('barcode')->nullable()->index();
            $table->string('name');
            $table->decimal('retail_price', 12, 2)->default(0.00); // বিক্রয় মূল্য
            $table->decimal('wholesale_price', 12, 2)->default(0.00); // পাইকারি মূল্য
            $table->decimal('cost_price', 12, 2)->default(0.00); // ক্রয় মূল্য
            $table->decimal('stock_quantity', 12, 2)->default(0.00);
            $table->decimal('alert_quantity', 12, 2)->default(5.00);
            $table->string('unit')->default('pcs'); // pcs, kg, litre, box
            $table->boolean('is_custom')->default(true); // লোকাল নিজস্ব পণ্য কিনা
            $table->string('photo')->nullable();
            $table->boolean('status')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations on tenant database.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};

