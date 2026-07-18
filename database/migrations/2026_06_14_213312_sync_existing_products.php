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
        // Get all unique SKUs in products table
        $skus = \App\Models\Product::pluck('sku')->unique();

        $branches = \App\Models\Branch::all();

        foreach ($skus as $sku) {
            $rep = \App\Models\Product::where('sku', $sku)->first();
            if (!$rep) {
                continue;
            }

            // Ensure SKU exists in Gudang (branch_id = null)
            $existsInGudang = \App\Models\Product::where('sku', $sku)->whereNull('branch_id')->exists();
            if (!$existsInGudang) {
                \App\Models\Product::create([
                    'brand' => $rep->brand,
                    'name' => $rep->name,
                    'sku' => $rep->sku,
                    'category' => $rep->category,
                    'is_digital' => $rep->is_digital,
                    'initial_stock' => $rep->is_digital ? null : 0,
                    'incoming_stock' => $rep->is_digital ? null : 0,
                    'final_stock' => $rep->is_digital ? null : 0,
                    'sold_stock' => $rep->is_digital ? null : 0,
                    'price' => $rep->price,
                    'hpp' => $rep->hpp,
                    'status' => $rep->is_digital ? null : 'Habis',
                    'branch_id' => null,
                ]);
            }

            // Ensure SKU exists in all branches
            foreach ($branches as $branch) {
                $existsInBranch = \App\Models\Product::where('sku', $sku)->where('branch_id', $branch->id)->exists();
                if (!$existsInBranch) {
                    \App\Models\Product::create([
                        'brand' => $rep->brand,
                        'name' => $rep->name,
                        'sku' => $rep->sku,
                        'category' => $rep->category,
                        'is_digital' => $rep->is_digital,
                        'initial_stock' => $rep->is_digital ? null : 0,
                        'incoming_stock' => $rep->is_digital ? null : 0,
                        'final_stock' => $rep->is_digital ? null : 0,
                        'sold_stock' => $rep->is_digital ? null : 0,
                        'price' => $rep->price,
                        'hpp' => $rep->hpp,
                        'status' => $rep->is_digital ? null : 'Habis',
                        'branch_id' => $branch->id,
                    ]);
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
