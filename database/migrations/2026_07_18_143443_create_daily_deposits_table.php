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
        Schema::create('daily_deposits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('branch_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            
            // Kolom rincian setoran
            $table->decimal('bendel_jutaan', 15, 2)->default(0);
            $table->decimal('bendel_puluhan', 15, 2)->default(0);
            $table->decimal('bendel_ribuan', 15, 2)->default(0);
            $table->decimal('koin', 15, 2)->default(0);
            
            $table->decimal('sisa_100_50', 15, 2)->default(0);
            $table->decimal('sisa_20_10_5', 15, 2)->default(0);
            $table->decimal('sisa_2_1', 15, 2)->default(0);
            $table->decimal('sisa_lain', 15, 2)->default(0);
            
            // Total dari semua rincian di atas
            $table->decimal('amount', 15, 2)->default(0);
            $table->text('description')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_deposits');
    }
};
