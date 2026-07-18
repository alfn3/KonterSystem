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
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('status'); // Online / Offline
            $table->string('load')->nullable(); // e.g. Sangat Ramai, Normal
            $table->decimal('revenue_mtd', 15, 2)->default(0);
            $table->integer('stock_available')->default(0);
            $table->integer('stock_health')->default(0);
            $table->string('address');
            $table->integer('profit_margin')->default(0);
            $table->string('cash_status');
            $table->boolean('cash_matched')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};
