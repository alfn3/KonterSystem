<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique('products_sku_unique');
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('cascade');
            $table->unique(['sku', 'branch_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropUnique(['sku', 'branch_id']);
            $table->dropForeign(['branch_id']);
            $table->dropColumn('branch_id');
            $table->unique('sku');
        });
    }
};
