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
            $table->string('agent_id')->nullable()->after('name');
        });

        // Seed initial agent IDs for default branches
        \Illuminate\Support\Facades\DB::table('branches')->where('name', 'mobil1')->update(['agent_id' => 'operator1']);
        \Illuminate\Support\Facades\DB::table('branches')->where('name', 'mobil2')->update(['agent_id' => 'operator2']);
        \Illuminate\Support\Facades\DB::table('branches')->where('name', 'toko')->update(['agent_id' => 'operator3']);
        \Illuminate\Support\Facades\DB::table('branches')->where('name', 'mobil4')->update(['agent_id' => 'operator4']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('branches', function (Blueprint $table) {
            $table->dropColumn('agent_id');
        });
    }
};
