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
        Schema::create('transactions', function (Blueprint $table) {
            $table->string('id')->primary(); // Using the reference_no/id from Mobile Counter
            $table->foreignId('branch_id')->constrained('branches')->onDelete('cascade');
            $table->decimal('total_amount', 15, 2);
            $table->string('payment_method');
            $table->decimal('cash_paid', 15, 2);
            $table->decimal('change', 15, 2);
            $table->string('payment_change')->nullable();
            $table->string('status');
            $table->string('customer_id')->nullable();
            $table->string('customer_phone')->nullable();
            $table->string('operator')->nullable();
            $table->timestamps();
        });

        Schema::create('transaction_items', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id');
            $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('set null');
            $table->string('product_sku');
            $table->string('product_name');
            $table->string('product_category');
            $table->integer('quantity');
            $table->decimal('price', 15, 2);
            $table->string('destination_number')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_items');
        Schema::dropIfExists('transactions');
    }
};
