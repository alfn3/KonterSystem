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
        Schema::create('weekly_performances', function (Blueprint $table) {
            $table->id();
            $table->string('week'); // e.g. Minggu 1
            $table->decimal('omzet', 15, 2)->default(0);
            $table->decimal('hpp', 15, 2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weekly_performances');
    }
};
