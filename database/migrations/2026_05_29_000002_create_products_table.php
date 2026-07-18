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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('brand');
            $table->string('name');
            $table->string('sku')->unique();
            $table->string('category'); // e.g. Perdana, Voucher, Aksesoris
            $table->boolean('is_digital')->default(false);
            $table->integer('initial_stock')->nullable();
            $table->integer('incoming_stock')->nullable();
            $table->integer('final_stock')->nullable();
            $table->integer('sold_stock')->nullable();
            $table->decimal('price', 15, 2)->default(0);
            $table->decimal('hpp', 15, 2)->default(0);
            $table->string('status')->nullable(); // e.g. Aman, Kritis, Tipis, Habis
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
