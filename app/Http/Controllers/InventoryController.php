<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $selectedBranch = $request->query('branch', 'Gudang');
        $selectedDate = $request->query('date', date('Y-m-d'));
        $branches = Branch::orderBy('name', 'asc')->get();

        // 1. Build Query based on selected branch
        $query = Product::query()->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris']);
        if ($selectedBranch === 'Gudang') {
            $query->whereNull('branch_id');
        } else {
            $branchObj = Branch::where('name', $selectedBranch)->first();
            if ($branchObj) {
                $query->where('branch_id', $branchObj->id);
            } else {
                $query->where('id', -1); // Force empty
            }
        }

        // Filter products created on or before the selected date
        $endOfDate = \Carbon\Carbon::parse($selectedDate)->endOfDay();
        $query->where('created_at', '<=', $endOfDate);

        // Load products collection
        $productsCollection = $query->get();

        // Adjust stock levels based on daily stock calculations: initial stock today = final stock yesterday
        $startOfDate = \Carbon\Carbon::parse($selectedDate)->startOfDay();
        $movementsAfterStart = \App\Models\StockMovement::where('created_at', '>=', $startOfDate)->get()->groupBy('product_id');
        $movementsAfterEnd = \App\Models\StockMovement::where('created_at', '>', $endOfDate)->get()->groupBy('product_id');

        foreach ($productsCollection as $p) {
            $movAfterStart = $movementsAfterStart->get($p->id) ?? collect();
            $movAfterEnd = $movementsAfterEnd->get($p->id) ?? collect();

            $changeAfterStart = $movAfterStart->sum('quantity_change');
            $changeAfterEnd = $movAfterEnd->sum('quantity_change');

            // Today's initial stock is yesterday's final stock
            $p->initial_stock = $p->final_stock - $changeAfterStart;

            // Today's final stock (Sisa/Akhir)
            $p->final_stock = $p->final_stock - $changeAfterEnd;

            // Today's movements
            $movToday = $movAfterStart->filter(fn($m) => $m->created_at <= $endOfDate);

            // Today's incoming stock (Topup)
            $p->incoming_stock = $movToday->where('type', 'Restok')->sum('quantity_change') 
                + $movToday->where('type', 'Mutasi')->where('quantity_change', '>', 0)->sum('quantity_change');

            // Today's sold stock (Terjual/Keluar)
            $p->sold_stock = abs($movToday->where('type', 'Penjualan')->sum('quantity_change')) 
                + abs($movToday->where('type', 'Mutasi')->where('quantity_change', '<', 0)->sum('quantity_change'));

            if ($p->final_stock == 0) {
                $p->status = 'Habis';
            } elseif ($p->final_stock <= 5) {
                $p->status = 'Kritis';
            } elseif ($p->final_stock <= 10) {
                $p->status = 'Tipis';
            } else {
                $p->status = 'Aman';
            }
        }

        // 2. Total Asset Value for current branch/gudang
        $totalAsset = $productsCollection->sum(fn($p) => $p->final_stock * $p->price);
        $asset_value = [
            'value' => 'Rp ' . number_format($totalAsset, 0, ',', '.'),
            'change' => '+1.2%',
        ];

        // 3. Product Rankings (Fast / Slow Moving)
        $fastMovingProducts = $productsCollection->sortByDesc('sold_stock')->take(2);
        $slowMovingProducts = $productsCollection->sortBy('sold_stock')->take(2);

        $rankings = [
            'fast_moving' => $fastMovingProducts->map(fn($p) => ['name' => $p->name, 'sold' => $p->sold_stock])->toArray(),
            'slow_moving' => $slowMovingProducts->map(fn($p) => ['name' => $p->name, 'sold' => $p->sold_stock])->toArray(),
        ];

        // 4. Low Stock List
        $lowStockProducts = $productsCollection->filter(fn($p) => $p->final_stock <= 10)->sortBy('final_stock')->take(3);
        $low_stock = $lowStockProducts->map(fn($p) => ['name' => $p->name, 'remaining' => $p->final_stock])->toArray();

        // 5. Detailed Inventory Table Data (Support category filter if provided)
        $tableCollection = $productsCollection;
        if ($request->has('category') && $request->category != 'Semua') {
            $tableCollection = $tableCollection->where('category', $request->category);
        }
        $categoryOrder = [
            'Perdana' => 1,
            'Voucher' => 2,
            'Aksesoris' => 3,
        ];

        $dbProducts = $tableCollection->sortBy(function ($product) use ($categoryOrder) {
            $catWeight = $categoryOrder[$product->category] ?? 999;
            return [
                $catWeight,
                strtolower($product->category),
                strtolower($product->brand),
                strtolower($product->name)
            ];
        });

        $products = [];
        foreach ($dbProducts as $p) {
            // Determine styling classes based on brand and category
            $brandClass = 'text-blue-500';
            if (in_array(strtolower($p->brand), ['xl', 'axis'])) {
                $brandClass = 'text-purple-500';
            } elseif (in_array(strtolower($p->brand), ['type-c', 'batok', 'aksesoris', 'charger', 'orico', 'apple'])) {
                $brandClass = 'text-amber-500';
            }

            $catClass = 'bg-blue-50 text-blue-700 border-blue-100';
            if ($p->category === 'Voucher') {
                $catClass = 'bg-purple-50 text-purple-700 border-purple-100';
            } elseif ($p->category === 'Aksesoris') {
                $catClass = 'bg-amber-50 text-amber-700 border-amber-100';
            }

            $statusColor = 'bg-commander-success';
            $statusTextColor = 'text-commander-success';
            if ($p->status === 'Kritis') {
                $statusColor = 'bg-commander-error';
                $statusTextColor = 'text-commander-error';
            } elseif ($p->status === 'Tipis') {
                $statusColor = 'bg-amber-500';
                $statusTextColor = 'text-amber-600';
            } elseif ($p->status === 'Habis') {
                $statusColor = 'bg-commander-error';
                $statusTextColor = 'text-commander-error';
            }

            $products[] = [
                'id' => $p->id,
                'brand' => $p->brand,
                'brand_class' => $brandClass,
                'name' => $p->name,
                'sku' => $p->sku,
                'category' => $p->category,
                'category_class' => $catClass,
                'initial' => $p->initial_stock,
                'incoming' => $p->incoming_stock,
                'incoming_warning' => ($p->incoming_stock > 0 && $p->final_stock <= 5),
                'final' => $p->final_stock,
                'sold' => -$p->sold_stock,
                'price' => (float)$p->price,
                'hpp' => (float)$p->hpp,
                'price_formatted' => 'Rp ' . number_format($p->price, 0, ',', '.'),
                'hpp_formatted' => 'Rp ' . number_format($p->hpp, 0, ',', '.'),
                'margin' => $p->price > 0 ? number_format((($p->price - $p->hpp) / $p->price) * 100, 1) . '%' : '0%',
                'status' => $p->status,
                'status_color' => $statusColor,
                'status_text_color' => $statusTextColor,
                'row_class' => $p->status === 'Habis' ? 'bg-red-50/20' : '',
            ];
        }

        // 6. Bottom Analytics
        $totalInitialIncoming = $productsCollection->sum(fn($p) => $p->initial_stock + $p->incoming_stock);
        $totalFinal = $productsCollection->sum('final_stock');
        $healthPct = $totalInitialIncoming > 0 ? ($totalFinal / $totalInitialIncoming) * 100 : 100;

        $topCat = $productsCollection->groupBy('category')
            ->map(fn($group) => $group->sum('sold_stock'))
            ->sortDesc()
            ->keys()
            ->first();
        $topCatName = $topCat ?? 'Tidak Ada';

        $stagnantCount = $productsCollection->where('sold_stock', 0)->count();

        $bottom_analytics = [
            'stock_health' => [
                'value' => number_format($healthPct, 1) . '%',
                'change' => '+2.4%',
                'desc' => 'Rata-rata ketersediaan di seluruh SKU',
            ],
            'top_category' => [
                'value' => $topCatName,
                'share' => 'Turnover Tertinggi',
                'desc' => 'Kategori dengan penjualan tertinggi',
            ],
            'stagnant_items' => [
                'value' => $stagnantCount,
                'alert' => 'Alert',
                'desc' => 'Item dengan pergerakan nol (stagnan)',
            ],
        ];

        // 7. Load all Gudang products for the Restock selection modal
        $gudangProducts = Product::whereNull('branch_id')
            ->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])
            ->where('created_at', '<=', $endOfDate)
            ->orderBy('name', 'asc')
            ->get();
        foreach ($gudangProducts as $gp) {
            $movAfterEnd = $movementsAfterEnd->get($gp->id) ?? collect();
            $changeAfterEnd = $movAfterEnd->sum('quantity_change');
            $gp->final_stock = $gp->final_stock - $changeAfterEnd;
        }

        return view('inventory.index', compact('asset_value', 'rankings', 'low_stock', 'products', 'bottom_analytics', 'branches', 'selectedBranch', 'gudangProducts', 'selectedDate'));
    }

    public function store(Request $request)
    {
        if ($request->has('price')) {
            $request->merge(['price' => preg_replace('/[^0-9]/', '', $request->input('price'))]);
        }
        if ($request->has('hpp')) {
            $request->merge(['hpp' => preg_replace('/[^0-9]/', '', $request->input('hpp'))]);
        }

        // Auto generate SKU if missing
        if (!$request->filled('sku')) {
            $sku = $this->generateSku($request->input('category'), $request->input('brand'), $request->input('name'));
            $request->merge(['sku' => $sku]);
        }

        $request->merge([
            'initial_stock' => (int)$request->input('initial_stock', 0),
            'incoming_stock' => (int)$request->input('incoming_stock', 0),
            'final_stock' => (int)$request->input('final_stock', 0),
            'sold_stock' => max(0, (int)$request->input('initial_stock', 0) + (int)$request->input('incoming_stock', 0) - (int)$request->input('final_stock', 0))
        ]);

        $validated = $request->validate([
            'brand' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->whereNull('branch_id'),
            ],
            'category' => ['required', 'string', 'max:50', Rule::in(['Perdana', 'Voucher', 'Aksesoris'])],
            'initial_stock' => 'required|integer|min:0',
            'incoming_stock' => 'required|integer|min:0',
            'final_stock' => 'required|integer|min:0',
            'sold_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'hpp' => 'required|numeric|min:0',
        ]);

        // Auto determine status
        $status = 'Aman';
        if ($validated['final_stock'] == 0) {
            $status = 'Habis';
        } elseif ($validated['final_stock'] <= 5) {
            $status = 'Kritis';
        } elseif ($validated['final_stock'] <= 10) {
            $status = 'Tipis';
        }

        $product = Product::create(array_merge($validated, [
            'status' => $status,
            'branch_id' => null, // Added to Gudang by default
        ]));

        // Replicate product to all branches
        if (is_null($product->branch_id)) {
            $branches = Branch::all();
            foreach ($branches as $branch) {
                Product::create([
                    'brand' => $product->brand,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'category' => $product->category,
                    'is_digital' => $product->is_digital ?? false,
                    'initial_stock' => 0,
                    'incoming_stock' => 0,
                    'final_stock' => 0,
                    'sold_stock' => 0,
                    'price' => $product->price,
                    'hpp' => $product->hpp,
                    'status' => 'Habis',
                    'branch_id' => $branch->id,
                ]);
            }
        }

        // Log as Correction/Inisiasi
        StockMovement::create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'product_sku' => $product->sku,
            'product_category' => $product->category,
            'branch_name' => 'Gudang',
            'quantity_change' => $product->final_stock,
            'final_stock' => $product->final_stock,
            'type' => 'Koreksi',
            'reference_no' => 'Tambah Produk Baru',
            'operator' => auth()->user()->name ?? 'Budi (Admin)',
        ]);

        return redirect()->back()->with('success', 'Produk berhasil ditambahkan ke inventoris!');
    }

    public function update(Request $request, $id)
    {
        $product = Product::findOrFail($id);
        $branchId = $product->branch_id;

        if ($request->has('price')) {
            $request->merge(['price' => preg_replace('/[^0-9]/', '', $request->input('price'))]);
        }
        if ($request->has('hpp')) {
            $request->merge(['hpp' => preg_replace('/[^0-9]/', '', $request->input('hpp'))]);
        }

        // Sync initial and final stock corrections
        $oldInitial = $product->initial_stock;
        $oldFinal = $product->final_stock;
        $newInitial = $request->has('initial_stock') ? (int)$request->input('initial_stock') : $oldInitial;
        $newFinal = $request->has('final_stock') ? (int)$request->input('final_stock') : $oldFinal;

        $diffInitial = $newInitial - $oldInitial;
        $diffFinal = $newFinal - $oldFinal;

        if ($diffInitial != 0 && $diffFinal == 0) {
            $request->merge(['final_stock' => $oldFinal + $diffInitial]);
        } elseif ($diffFinal != 0 && $diffInitial == 0) {
            $request->merge(['initial_stock' => $oldInitial + $diffFinal]);
        }

        $request->merge([
            'sold_stock' => max(0, (int)$request->input('initial_stock', 0) + (int)$request->input('incoming_stock', 0) - (int)$request->input('final_stock', 0))
        ]);

        $validated = $request->validate([
            'brand' => 'required|string|max:50',
            'name' => 'required|string|max:100',
            'sku' => [
                'required',
                'string',
                'max:50',
                Rule::unique('products', 'sku')->ignore($id)->where(function ($q) use ($branchId) {
                    if (is_null($branchId)) {
                        $q->whereNull('branch_id');
                    } else {
                        $q->where('branch_id', $branchId);
                    }
                }),
            ],
            'category' => ['required', 'string', 'max:50', Rule::in(['Perdana', 'Voucher', 'Aksesoris'])],
            'initial_stock' => 'required|integer|min:0',
            'incoming_stock' => 'required|integer|min:0',
            'final_stock' => 'required|integer|min:0',
            'sold_stock' => 'required|integer|min:0',
            'price' => 'required|numeric|min:0',
            'hpp' => 'required|numeric|min:0',
        ]);

        $oldFinalStock = $product->final_stock;
        $newFinalStock = $validated['final_stock'];

        // Auto determine status
        $status = 'Aman';
        if ($newFinalStock == 0) {
            $status = 'Habis';
        } elseif ($newFinalStock <= 5) {
            $status = 'Kritis';
        } elseif ($newFinalStock <= 10) {
            $status = 'Tipis';
        }
        $validated['status'] = $status;

        // Save old detail values before update
        $oldName = $product->name;
        $oldHpp = $product->hpp;
        $oldPrice = $product->price;

        $product->update($validated);

        $details = [];
        if ($oldName !== $validated['name']) {
            $details[] = "Nama (" . $oldName . " -> " . $validated['name'] . ")";
        }
        if ((float)$oldHpp !== (float)$validated['hpp']) {
            $details[] = "HPP (Rp " . number_format($oldHpp, 0, ',', '.') . " -> Rp " . number_format($validated['hpp'], 0, ',', '.') . ")";
        }
        if ((float)$oldPrice !== (float)$validated['price']) {
            $details[] = "Harga Jual (Rp " . number_format($oldPrice, 0, ',', '.') . " -> Rp " . number_format($validated['price'], 0, ',', '.') . ")";
        }

        $branchName = $product->branch_id ? ($product->branch->name ?? 'Cabang') : 'Gudang';

        if (!empty($details)) {
            $ref = implode(', ', $details);
            StockMovement::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_category' => $product->category,
                'branch_name' => $branchName,
                'quantity_change' => 0,
                'final_stock' => $newFinalStock,
                'type' => 'Koreksi',
                'reference_no' => 'Koreksi: ' . $ref,
                'operator' => auth()->user()->name ?? 'Budi (Admin)',
            ]);
        }

        // Log to stock movements if final stock changed
        if ($oldFinalStock != $newFinalStock) {
            $diff = $newFinalStock - $oldFinalStock;
            
            StockMovement::create([
                'product_id' => $product->id,
                'product_name' => $product->name,
                'product_sku' => $product->sku,
                'product_category' => $product->category,
                'branch_name' => $branchName,
                'quantity_change' => $diff,
                'final_stock' => $newFinalStock,
                'type' => 'Koreksi',
                'reference_no' => 'Correction/Edit-Manual/' . $product->sku,
                'operator' => auth()->user()->name ?? 'Budi (Admin)',
            ]);
        }

        return redirect()->back()->with('success', 'Produk berhasil diperbarui!');
    }

    public function history(Request $request)
    {
        $query = StockMovement::query();

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('product_name', 'like', "%{$search}%")
                  ->orWhere('product_sku', 'like', "%{$search}%")
                  ->orWhere('reference_no', 'like', "%{$search}%");
            });
        }

        if ($request->has('type') && $request->type != 'Semua Tipe') {
            $query->where('type', $request->type);
        }

        if ($request->has('date') && !empty($request->date)) {
            $query->whereDate('created_at', $request->date);
        }

        $movements = $query->orderBy('created_at', 'desc')->paginate(10);

        return view('inventory.history', compact('movements'));
    }

    public function restock(Request $request)
    {
        $validated = $request->validate([
            'branch_name' => 'required|string',
            'supplier' => 'nullable|string',
            'reference_no' => 'required|string',
            'items' => 'required|array',
            'items.*.sku' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        $branchName = $validated['branch_name'];
        $supplier = $validated['supplier'] ?? 'Gudang Pusat';
        $refNo = $validated['reference_no'];
        $items = $validated['items'];

        if ($branchName === 'Gudang') {
            if (strpos($supplier, 'Cabang: ') === 0) {
                // Mutasi: Penarikan dari Cabang ke Gudang
                $sourceBranch = str_replace('Cabang: ', '', $supplier);
                $sourceBranchObj = Branch::where('name', $sourceBranch)->firstOrFail();

                foreach ($items as $item) {
                    $sku = $item['sku'];
                    $qty = $item['quantity'];

                    // Deduct from Cabang Product
                    $cabangProduct = Product::where('sku', $sku)->where('branch_id', $sourceBranchObj->id)->firstOrFail();
                    $cabangProduct->final_stock -= $qty;
                    $cabangProduct->status = $this->determineStatus($cabangProduct->final_stock);
                    $cabangProduct->save();

                    // Add to Gudang Product
                    $gudangProduct = Product::where('sku', $sku)->whereNull('branch_id')->firstOrFail();
                    $gudangProduct->final_stock += $qty;
                    $gudangProduct->status = $this->determineStatus($gudangProduct->final_stock);
                    $gudangProduct->save();

                    // Log movements (Mutasi)
                    $mutationName = "{$sourceBranch} > Gudang";
                    StockMovement::create([
                        'product_id' => $cabangProduct->id,
                        'product_name' => $cabangProduct->name,
                        'product_sku' => $cabangProduct->sku,
                        'product_category' => $cabangProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => -$qty,
                        'final_stock' => $cabangProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);

                    StockMovement::create([
                        'product_id' => $gudangProduct->id,
                        'product_name' => $gudangProduct->name,
                        'product_sku' => $gudangProduct->sku,
                        'product_category' => $gudangProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => $qty,
                        'final_stock' => $gudangProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);
                }

                // Update source branch stock available
                $totalSourceStock = Product::where('branch_id', $sourceBranchObj->id)->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])->sum('final_stock');
                $sourceBranchObj->update(['stock_available' => $totalSourceStock]);

            } else {
                // Restok: Gudang dari Supplier
                foreach ($items as $item) {
                    $sku = $item['sku'];
                    $qty = $item['quantity'];

                    $gudangProduct = Product::where('sku', $sku)->whereNull('branch_id')->firstOrFail();
                    $gudangProduct->incoming_stock += $qty;
                    $gudangProduct->final_stock += $qty;
                    $gudangProduct->status = $this->determineStatus($gudangProduct->final_stock);
                    $gudangProduct->save();

                    StockMovement::create([
                        'product_id' => $gudangProduct->id,
                        'product_name' => $gudangProduct->name,
                        'product_sku' => $gudangProduct->sku,
                        'product_category' => $gudangProduct->category,
                        'branch_name' => 'Gudang',
                        'quantity_change' => $qty,
                        'final_stock' => $gudangProduct->final_stock,
                        'type' => 'Restok',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);
                }
            }
        } else {
            // Target is a Cabang
            $targetBranchObj = Branch::where('name', $branchName)->firstOrFail();

            if (strpos($supplier, 'Cabang: ') === 0) {
                // Mutasi: Cabang ke Cabang
                $sourceBranch = str_replace('Cabang: ', '', $supplier);
                $sourceBranchObj = Branch::where('name', $sourceBranch)->firstOrFail();

                foreach ($items as $item) {
                    $sku = $item['sku'];
                    $qty = $item['quantity'];

                    // Deduct from source Cabang
                    $sourceProduct = Product::where('sku', $sku)->where('branch_id', $sourceBranchObj->id)->firstOrFail();
                    $sourceProduct->final_stock -= $qty;
                    $sourceProduct->status = $this->determineStatus($sourceProduct->final_stock);
                    $sourceProduct->save();

                    // Add to target Cabang
                    $targetProduct = Product::where('sku', $sku)->where('branch_id', $targetBranchObj->id)->first();
                    if (!$targetProduct) {
                        $targetProduct = new Product([
                            'brand' => $sourceProduct->brand,
                            'name' => $sourceProduct->name,
                            'sku' => $sku,
                            'category' => $sourceProduct->category,
                            'initial_stock' => 0,
                            'incoming_stock' => 0,
                            'final_stock' => 0,
                            'sold_stock' => 0,
                            'price' => $sourceProduct->price,
                            'hpp' => $sourceProduct->hpp,
                            'status' => 'Habis',
                            'branch_id' => $targetBranchObj->id,
                        ]);
                    }

                    $targetProduct->incoming_stock += $qty;
                    $targetProduct->final_stock += $qty;
                    $targetProduct->status = $this->determineStatus($targetProduct->final_stock);
                    $targetProduct->save();

                    // Log movements (Mutasi)
                    $mutationName = "{$sourceBranch} > {$branchName}";
                    StockMovement::create([
                        'product_id' => $sourceProduct->id,
                        'product_name' => $sourceProduct->name,
                        'product_sku' => $sourceProduct->sku,
                        'product_category' => $sourceProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => -$qty,
                        'final_stock' => $sourceProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);

                    StockMovement::create([
                        'product_id' => $targetProduct->id,
                        'product_name' => $targetProduct->name,
                        'product_sku' => $targetProduct->sku,
                        'product_category' => $targetProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => $qty,
                        'final_stock' => $targetProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);
                }

                // Update both branches stock_available
                $totalSourceStock = Product::where('branch_id', $sourceBranchObj->id)->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])->sum('final_stock');
                $sourceBranchObj->update(['stock_available' => $totalSourceStock]);

                $totalTargetStock = Product::where('branch_id', $targetBranchObj->id)->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])->sum('final_stock');
                $targetBranchObj->update(['stock_available' => $totalTargetStock]);

            } else {
                // Mutasi: Gudang ke Cabang
                foreach ($items as $item) {
                    $sku = $item['sku'];
                    $qty = $item['quantity'];

                    // Deduct from Gudang Product
                    $gudangProduct = Product::where('sku', $sku)->whereNull('branch_id')->firstOrFail();
                    $gudangProduct->final_stock -= $qty;
                    $gudangProduct->status = $this->determineStatus($gudangProduct->final_stock);
                    $gudangProduct->save();

                    // Add to target Cabang
                    $targetProduct = Product::where('sku', $sku)->where('branch_id', $targetBranchObj->id)->first();
                    if (!$targetProduct) {
                        $targetProduct = new Product([
                            'brand' => $gudangProduct->brand,
                            'name' => $gudangProduct->name,
                            'sku' => $sku,
                            'category' => $gudangProduct->category,
                            'initial_stock' => 0,
                            'incoming_stock' => 0,
                            'final_stock' => 0,
                            'sold_stock' => 0,
                            'price' => $gudangProduct->price,
                            'hpp' => $gudangProduct->hpp,
                            'status' => 'Habis',
                            'branch_id' => $targetBranchObj->id,
                        ]);
                    }

                    $targetProduct->incoming_stock += $qty;
                    $targetProduct->final_stock += $qty;
                    $targetProduct->status = $this->determineStatus($targetProduct->final_stock);
                    $targetProduct->save();

                    // Log movements (Mutasi)
                    $mutationName = "Gudang > {$branchName}";
                    StockMovement::create([
                        'product_id' => $gudangProduct->id,
                        'product_name' => $gudangProduct->name,
                        'product_sku' => $gudangProduct->sku,
                        'product_category' => $gudangProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => -$qty,
                        'final_stock' => $gudangProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);

                    StockMovement::create([
                        'product_id' => $targetProduct->id,
                        'product_name' => $targetProduct->name,
                        'product_sku' => $targetProduct->sku,
                        'product_category' => $targetProduct->category,
                        'branch_name' => $mutationName,
                        'quantity_change' => $qty,
                        'final_stock' => $targetProduct->final_stock,
                        'type' => 'Mutasi',
                        'reference_no' => $refNo,
                        'operator' => auth()->user()->name ?? 'Budi (Admin)',
                    ]);
                }

                $totalTargetStock = Product::where('branch_id', $targetBranchObj->id)->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris'])->sum('final_stock');
                $targetBranchObj->update(['stock_available' => $totalTargetStock]);
            }
        }

        return redirect()->back()->with('success', 'Restok berhasil dilakukan!');
    }

    private function determineStatus($finalStock)
    {
        if ($finalStock == 0) {
            return 'Habis';
        } elseif ($finalStock <= 5) {
            return 'Kritis';
        } elseif ($finalStock <= 10) {
            return 'Tipis';
        }
        return 'Aman';
    }

    public function analytics(Request $request)
    {
        $selectedBranch = $request->query('branch', 'Gudang');
        $selectedDate = $request->query('date', date('Y-m-d'));
        $branches = Branch::orderBy('name', 'asc')->get();

        $query = Product::query()->whereIn('category', ['Perdana', 'Voucher', 'Aksesoris']);
        if ($selectedBranch === 'Gudang') {
            $query->whereNull('branch_id');
        } else {
            $branchObj = Branch::where('name', $selectedBranch)->first();
            if ($branchObj) {
                $query->where('branch_id', $branchObj->id);
            } else {
                $query->where('id', -1);
            }
        }

        // Filter products created on or before the selected date
        $endOfDate = \Carbon\Carbon::parse($selectedDate)->endOfDay();
        $query->where('created_at', '<=', $endOfDate);

        // Load products collection
        $productsCollection = $query->get();

        // Adjust stock levels based on daily stock calculations: initial stock today = final stock yesterday
        $startOfDate = \Carbon\Carbon::parse($selectedDate)->startOfDay();
        $movementsAfterStart = \App\Models\StockMovement::where('created_at', '>=', $startOfDate)->get()->groupBy('product_id');
        $movementsAfterEnd = \App\Models\StockMovement::where('created_at', '>', $endOfDate)->get()->groupBy('product_id');

        foreach ($productsCollection as $p) {
            $movAfterStart = $movementsAfterStart->get($p->id) ?? collect();
            $movAfterEnd = $movementsAfterEnd->get($p->id) ?? collect();

            $changeAfterStart = $movAfterStart->sum('quantity_change');
            $changeAfterEnd = $movAfterEnd->sum('quantity_change');

            // Today's initial stock is yesterday's final stock
            $p->initial_stock = $p->final_stock - $changeAfterStart;

            // Today's final stock
            $p->final_stock = $p->final_stock - $changeAfterEnd;

            // Today's movements
            $movToday = $movAfterStart->filter(fn($m) => $m->created_at <= $endOfDate);

            // Today's incoming stock
            $p->incoming_stock = $movToday->where('type', 'Restok')->sum('quantity_change') 
                + $movToday->where('type', 'Mutasi')->where('quantity_change', '>', 0)->sum('quantity_change');

            // Today's sold stock
            $p->sold_stock = abs($movToday->where('type', 'Penjualan')->sum('quantity_change')) 
                + abs($movToday->where('type', 'Mutasi')->where('quantity_change', '<', 0)->sum('quantity_change'));
        }

        $totalAsset = $productsCollection->sum(fn($p) => $p->final_stock * $p->price);
        $totalHppAsset = $productsCollection->sum(fn($p) => $p->final_stock * $p->hpp);
        $potentialProfit = $totalAsset - $totalHppAsset;
        $totalItems = $productsCollection->sum('final_stock');

        $categoryAssets = $productsCollection->groupBy('category')
            ->map(fn($group, $cat) => [
                'category' => $cat,
                'asset_value' => $group->sum(fn($p) => $p->final_stock * $p->price),
                'total_qty' => $group->sum('final_stock'),
            ])
            ->sortByDesc('asset_value')
            ->values()
            ->toArray();

        $fastMoving = $productsCollection->sortByDesc('sold_stock')->take(10);
        $slowMoving = $productsCollection->sortBy('sold_stock')->take(10);
        $lowStock = $productsCollection->filter(fn($p) => $p->final_stock <= 10)->sortBy('final_stock');

        return view('inventory.analytics', compact(
            'branches', 
            'selectedBranch', 
            'totalAsset', 
            'totalHppAsset', 
            'potentialProfit', 
            'totalItems', 
            'categoryAssets',
            'fastMoving', 
            'slowMoving', 
            'lowStock',
            'selectedDate'
        ));
    }

    public function destroy($id)
    {
        $product = Product::findOrFail($id);
        $sku = $product->sku;

        // Check if any branch (including Gudang) has final_stock > 0 for this SKU
        $totalStock = Product::where('sku', $sku)->sum('final_stock');

        if ($totalStock > 0) {
            return redirect()->back()->withErrors([
                'delete_error' => "Produk '{$product->name}' (SKU: {$sku}) tidak bisa dihapus karena masih memiliki total stok {$totalStock} unit di cabang atau gudang."
            ]);
        }

        // Delete all products with this SKU across Gudang and all branches
        Product::where('sku', $sku)->delete();

        return redirect()->back()->with('success', 'Produk berhasil dihapus dari semua cabang dan gudang!');
    }

    private function generateSku($category, $brand, $name)
    {
        // Category prefix
        $catPrefix = 'PROD';
        $catLower = strtolower($category);
        if (str_contains($catLower, 'perd')) {
            $catPrefix = 'PDN';
        } elseif (str_contains($catLower, 'vouch')) {
            $catPrefix = 'VCH';
        } elseif (str_contains($catLower, 'akses') || str_contains($catLower, 'access')) {
            $catPrefix = 'ACC';
        } else {
            $catPrefix = strtoupper(substr(preg_replace('/[^a-zA-Z0-9]/', '', $category), 0, 3));
        }

        // Brand prefix
        $brandClean = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $brand));
        $brandPrefix = substr($brandClean, 0, 4);
        if ($brandClean === 'TELKOMSEL') {
            $brandPrefix = 'TSEL';
        } elseif ($brandClean === 'SMARTFREN') {
            $brandPrefix = 'SF';
        } elseif ($brandClean === 'INDOSAT') {
            $brandPrefix = 'ISAT';
        }

        // Name part
        $nameClean = strtoupper(preg_replace('/[^A-Z0-9]/', '', $name));
        $nameClean = str_replace([strtoupper($brand), strtoupper($category)], '', $nameClean);
        $nameClean = preg_replace('/[^A-Z0-9]/', '', $nameClean);
        
        $namePart = substr($nameClean, 0, 6);
        if (empty($namePart)) {
            $namePart = strtoupper(substr(md5($name . time()), 0, 4));
        }

        $baseSku = $catPrefix . '-' . $brandPrefix . '-' . $namePart;
        
        // Ensure uniqueness
        $sku = $baseSku;
        $counter = 1;
        while (\App\Models\Product::where('sku', $sku)->whereNull('branch_id')->exists()) {
            $sku = $baseSku . '-' . $counter;
            $counter++;
        }

        return $sku;
    }
}
