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
        Schema::create('cashier_reconciliations', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('shift');
            $table->decimal('sales', 15, 2)->default(0);
            $table->decimal('gap', 15, 2)->default(0);
            $table->decimal('bon', 15, 2)->default(0);
            $table->decimal('incentive', 15, 2)->default(0);
            $table->string('status'); // e.g. Matching, Discrepancy, Surplus
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cashier_reconciliations');
    }
};
