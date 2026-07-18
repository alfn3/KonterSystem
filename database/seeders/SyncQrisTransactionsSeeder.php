<?php

namespace Database\Seeders;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\StockMovement;
use App\Models\Branch;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncQrisTransactionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apiKey = config('services.temanqris.api_key');
        
        if (!$apiKey) {
            $this->command->error('TEMANQRIS_API_KEY is not configured in .env or services.php');
            return;
        }

        $this->command->info('Fetching payment links from TemanQRIS...');
        
        try {
            $response = Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Accept' => 'application/json',
            ])->get('https://temanqris.com/api/qris/payment-links');

            if (!$response->successful()) {
                $this->command->error('Failed to fetch from TemanQRIS: ' . $response->body());
                return;
            }

            $data = $response->json();
            $paymentLinks = $data['payment_links'] ?? [];

            if (empty($paymentLinks)) {
                $this->command->warn('No payment links returned from TemanQRIS API.');
                return;
            }

            $this->command->info('Found ' . count($paymentLinks) . ' payment links. Cleansing local QRIS data...');

            DB::transaction(function () use ($paymentLinks) {
                // Delete stock movements related to QRIS
                StockMovement::where('payment_method', 'QRIS')->delete();

                // Delete all existing transactions with payment method QRIS
                // Cascading delete will automatically remove related transaction_items
                Transaction::where('payment_method', 'QRIS')->delete();

                // Default branch (mobil 1 or first branch)
                $branch = Branch::where('name', 'mobil 1')->first() ?: Branch::first();
                $branchId = $branch ? $branch->id : 1;

                foreach ($paymentLinks as $link) {
                    $orderId = $link['order_id'] ?? null;
                    
                    if (!$orderId) {
                        continue;
                    }

                    // Map status
                    // TemanQRIS status: pending, active, paid, expired, failed
                    $rawStatus = strtolower($link['status'] ?? 'pending');
                    $dbStatus = 'Diproses'; // Default
                    if ($rawStatus === 'paid' || $rawStatus === 'confirmed') {
                        $dbStatus = 'Sukses';
                    } elseif ($rawStatus === 'expired' || $rawStatus === 'failed') {
                        $dbStatus = 'Gagal';
                    }

                    $amount = $link['amount'] ?? 0;
                    $createdAt = isset($link['created_at']) 
                        ? Carbon::parse($link['created_at'])->setTimezone(config('app.timezone', 'Asia/Jakarta')) 
                        : now();
                    
                    $changeText = 'Pas';

                    // Create transaction
                    $transaction = Transaction::create([
                        'id' => $orderId,
                        'branch_id' => $branchId,
                        'total_amount' => $amount,
                        'payment_method' => 'QRIS',
                        'cash_paid' => $amount,
                        'change' => 0,
                        'payment_change' => $changeText,
                        'qris' => null, // List API does not contain the raw QRIS string directly, uses fallback locally
                        'status' => $dbStatus,
                        'customer_id' => 'Pelanggan',
                        'customer_phone' => '081234567890',
                        'operator' => 'Andini (Kasir)',
                        'saldo_elektrik_remaining' => $branch ? $branch->saldo_elektrik : 0,
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ]);

                    // Create item
                    TransactionItem::create([
                        'transaction_id' => $transaction->id,
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
                    
                    $this->command->line("Imported QRIS Transaction: {$orderId} - Rp " . number_format($amount, 0, ',', '.') . " [{$dbStatus}]");
                }
            });

            $this->command->info('QRIS Data synchronisation completed successfully!');

        } catch (\Exception $e) {
            $this->command->error('An exception occurred during synchronization: ' . $e->getMessage());
            Log::error('QRIS Sync Seeder Exception: ' . $e->getMessage());
        }
    }
}
