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
        Schema::table('branches', function (Blueprint $table) {
            $table->decimal('saldo_elektrik', 15, 2)->default(0);
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->decimal('saldo_elektrik_remaining', 15, 2)->nullable();
        });

        Schema::table('stock_movements', function (Blueprint $table) {
            $table->decimal('saldo_elektrik_remaining', 15, 2)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropColumn('saldo_elektrik_remaining');
        });

        Schema::table('transactions', function (Blueprint $table) {
            $table->dropColumn('saldo_elektrik_remaining');
        });

        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('saldo_elektrik');
        });
    }
};
