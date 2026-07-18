<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class QrisPaymentController extends Controller
{
    /**
     * Display a listing of the QRIS transactions.
     */
    public function index(Request $request)
    {
        $apiKey = config('services.temanqris.api_key');

        if ($apiKey && (!app()->environment('testing') || config('services.temanqris.sync_in_tests'))) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'X-API-Key' => $apiKey,
                    'Accept' => 'application/json',
                ])->timeout(5)->get('https://temanqris.com/api/qris/payment-links');

                if ($response->successful()) {
                    $data = $response->json();
                    $paymentLinks = $data['payment_links'] ?? [];

                    if (!empty($paymentLinks)) {
                        // Default branch (mobil 1, mobil1, or first branch)
                        $branch = Branch::where('name', 'mobil 1')->first()
                            ?: Branch::where('name', 'mobil1')->first()
                            ?: Branch::first();
                        $branchId = $branch ? $branch->id : 1;

                        DB::transaction(function () use ($paymentLinks, $branchId, $branch) {
                            foreach ($paymentLinks as $link) {
                                $orderId = $link['order_id'] ?? null;
                                if (!$orderId) {
                                    continue;
                                }

                                // Map status
                                $rawStatus = strtolower($link['status'] ?? 'pending');
                                $dbStatus = 'Diproses'; // Default
                                if ($rawStatus === 'paid' || $rawStatus === 'confirmed') {
                                    $dbStatus = 'Sukses';
                                } elseif ($rawStatus === 'expired' || $rawStatus === 'failed') {
                                    $dbStatus = 'Gagal';
                                }

                                $amount = $link['amount'] ?? 0;
                                $createdAt = isset($link['created_at'])
                                    ? \Carbon\Carbon::parse($link['created_at'])->setTimezone(config('app.timezone', 'Asia/Jakarta'))
                                    : now();

                                 $transaction = Transaction::find($orderId);
                                if ($transaction) {
                                    // If status is different, not already Sukses locally, and not overwriting awaiting_confirmation with Diproses
                                    if ($transaction->status !== $dbStatus && $transaction->status !== 'Sukses' && ($transaction->status !== 'awaiting_confirmation' || $dbStatus === 'Sukses')) {
                                        $transaction->update(['status' => $dbStatus]);

                                        // If transition to Sukses, update branch revenue
                                        if ($dbStatus === 'Sukses') {
                                            $txBranch = $transaction->branch;
                                            if ($txBranch) {
                                                $txBranch->revenue_mtd += $transaction->total_amount;
                                                $txBranch->save();
                                            }
                                        }
                                    }

                                    // Sync QRIS string if available
                                    $qrisString = $link['qris'] ?? null;
                                    if ($qrisString && !$transaction->qris) {
                                        $transaction->update(['qris' => $qrisString]);
                                    }
                                } else {
                                    // Create new transaction
                                    $newTx = Transaction::create([
                                        'id' => $orderId,
                                        'branch_id' => $branchId,
                                        'total_amount' => $amount,
                                        'payment_method' => 'QRIS',
                                        'cash_paid' => $amount,
                                        'change' => 0,
                                        'payment_change' => 'Pas',
                                        'qris' => $link['qris'] ?? null,
                                        'status' => $dbStatus,
                                        'customer_id' => 'Pelanggan',
                                        'customer_phone' => '081234567890',
                                        'operator' => 'Andini (Kasir)',
                                        'saldo_elektrik_remaining' => $branch ? $branch->saldo_elektrik : 0,
                                        'created_at' => $createdAt,
                                        'updated_at' => $createdAt,
                                    ]);

                                    // Create default item
                                    \App\Models\TransactionItem::create([
                                        'transaction_id' => $newTx->id,
                                        'product_id' => null,
                                        'product_sku' => 'QRIS',
                                        'product_name' => 'Pembayaran QRIS',
                                        'product_category' => 'Digital',
                                        'quantity' => 1,
                                        'price' => $amount,
                                        'destination_number' => '081234567890',
                                        'created_at' => $createdAt,
                                        'updated_at' => $createdAt,
                                    ]);

                                    // If status is Sukses, update branch revenue
                                    if ($dbStatus === 'Sukses' && $branch) {
                                        $branch->revenue_mtd += $amount;
                                        $branch->save();
                                    }
                                }
                            }
                        });
                    }
                }
            } catch (\Exception $e) {
                Log::error('QRIS Auto-Sync on Page Load Failed: ' . $e->getMessage());
            }
        }

        $query = Transaction::where('payment_method', 'QRIS')->with(['branch', 'items']);

        // Filter by branch
        if ($request->filled('branch_id')) {
            $query->where('branch_id', $request->branch_id);
        }

        // Filter by status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Filter by date range
        if ($request->filled('start_date')) {
            $query->whereDate('created_at', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('created_at', '<=', $request->end_date);
        }

        // Search by Transaction ID or Customer Phone
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('id', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(15);
        $branches = Branch::orderBy('name', 'asc')->get();

        // Calculate statistics for QRIS payments
        $stats = [
            'total_count' => Transaction::where('payment_method', 'QRIS')->count(),
            'pending_count' => Transaction::where('payment_method', 'QRIS')->whereIn('status', ['Diproses', 'awaiting_confirmation'])->count(),
            'success_count' => Transaction::where('payment_method', 'QRIS')->where('status', 'Sukses')->count(),
            'failed_count' => Transaction::where('payment_method', 'QRIS')->where('status', 'Gagal')->count(),
            'total_amount' => Transaction::where('payment_method', 'QRIS')->where('status', 'Sukses')->sum('total_amount'),
        ];

        return view('qris.index', compact('transactions', 'branches', 'stats'));
    }

    /**
     * Confirm a QRIS transaction status manually.
     */
    public function confirm($id)
    {
        $transaction = Transaction::findOrFail($id);

        if ($transaction->payment_method !== 'QRIS') {
            return redirect()->back()->withErrors(['error' => 'Transaksi ini bukan transaksi QRIS.']);
        }

        if ($transaction->status === 'Sukses') {
            return redirect()->back()->with('info', 'Transaksi QRIS ini sudah sukses.');
        }

        try {
            DB::transaction(function () use ($transaction) {
                $transaction->update(['status' => 'Sukses']);

                $branch = $transaction->branch;
                if ($branch) {
                    $branch->revenue_mtd += $transaction->total_amount;
                    $branch->save();
                }
            });

            Log::info("Manual QRIS confirmation successful for transaction: {$id}");

            return redirect()->back()->with('success', "Transaksi {$id} berhasil dikonfirmasi secara manual.");
        } catch (\Exception $e) {
            Log::error("Manual QRIS confirmation failed for transaction {$id}: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal mengonfirmasi transaksi: ' . $e->getMessage()]);
        }
    }
}
