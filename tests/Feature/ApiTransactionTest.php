<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Product;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    public function test_get_saldo_elektrik_endpoint(): void
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $branch->saldo_elektrik = 10000000;
        $branch->save();

        $response = $this->getJson('/api/saldo-elektrik');

        $response->assertStatus(200)
            ->assertJson([
                'saldo_elektrik' => 10000000
            ]);
    }

    public function test_digital_transaction_deducts_only_hpp(): void
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $initialBalance = 5000000;
        $branch->saldo_elektrik = $initialBalance;
        $branch->save();

        // Ensure there is a digital product in DB
        $product = Product::create([
            'brand' => 'Telkomsel',
            'name' => 'Pulsa Telkomsel 10.000',
            'sku' => 'p_tsel_10_test',
            'category' => 'PULSA',
            'is_digital' => true,
            'price' => 12000,
            'hpp' => 10500,
            'branch_id' => $branch->id,
        ]);

        $payload = [
            'id' => 'TX-TEST-DIGITAL-HPP',
            'total_amount' => 12000,
            'payment_method' => 'Saldo Agen',
            'cash_paid' => 12000,
            'change' => 0,
            'status' => 'Sukses',
            'items' => [
                [
                    'product_id' => 'p_tsel_10_test',
                    'product_name' => 'Pulsa Telkomsel 10.000',
                    'product_category' => 'PULSA',
                    'quantity' => 1,
                    'price' => 12000,
                    'customer_phone' => '081234567890',
                    'destination_number' => '081234567890',
                    'operator_name' => 'Telkomsel'
                ]
            ]
        ];

        $response = $this->postJson('/api/transaksi', $payload);
        $response->assertStatus(200);

        $branch->refresh();
        // The balance should only be deducted by HPP (10,500), not retail price (12,000) or both (22,500)
        $expectedBalance = $initialBalance - 10500;
        $this->assertEquals($expectedBalance, (double)$branch->saldo_elektrik);

        // Verify the transaction record stores the remaining balance correctly
        $transaction = Transaction::find('TX-TEST-DIGITAL-HPP');
        $this->assertNotNull($transaction);
        $this->assertEquals($expectedBalance, (double)$transaction->saldo_elektrik_remaining);
    }

    public function test_generate_qris_endpoint(): void
    {
        \Illuminate\Support\Facades\Http::fake([
            'https://temanqris.com/api/qris/*' => \Illuminate\Support\Facades\Http::response([
                'success' => true,
                'qris' => '00020101021226580009COM.DUMMY123456789012345678905204000053033605405100005802ID5916SAHABAT COUNTER6006BEKASI610517121630489AB',
                'qr_image' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=',
                'amount' => 15000,
                'expires_at' => now()->addMinutes(15)->toDateTimeString(),
            ], 200)
        ]);

        $response = $this->postJson('/api/transaksi/qris', [
            'amount' => 15000,
            'order_id' => 'TX-QRIS-TEST-1',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'qris' => '00020101021226580009COM.DUMMY123456789012345678905204000053033605405100005802ID5916SAHABAT COUNTER6006BEKASI610517121630489AB',
                'amount' => 15000,
            ])
            ->assertJsonStructure(['success', 'qris', 'qr_image', 'amount', 'expires_at']);
    }

    public function test_payment_callback_and_status_checks(): void
    {
        $body = [
            'order_id' => 'TX-QRIS-TEST-2',
            'status' => 'paid',
            'event' => 'payment.confirmed',
        ];
        
        $secret = env('TEMANQRIS_WEBHOOK_SECRET') ?: 'd8b941a5116e4e31b5bfb89f5e4fa9ad077a368a53be4c618ac3c475a7fd5e78';
        $signature = 'sha256=' . hash_hmac('sha256', json_encode($body), $secret);

        // 1. Send callback for a transaction that doesn't exist in DB yet (race condition)
        $response = $this->postJson('/api/callback/payment', $body, [
            'X-TemanQRIS-Signature' => $signature
        ]);
        $response->assertStatus(200);
        $this->assertTrue(\Illuminate\Support\Facades\Cache::has('payment_success_TX-QRIS-TEST-2'));

        // 2. Poll status for the unregistered transaction (should return Sukses because it's cached)
        $response = $this->getJson('/api/transaksi/TX-QRIS-TEST-2/status');
        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'status' => 'Sukses',
            ]);

        // 3. Save transaction with status 'Diproses' (should automatically become 'Sukses' because of cached payment)
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $product = Product::create([
            'brand' => 'Telkomsel',
            'name' => 'Pulsa Telkomsel 10.000',
            'sku' => 'p_tsel_10_test_2',
            'category' => 'PULSA',
            'is_digital' => true,
            'price' => 12000,
            'hpp' => 10500,
            'branch_id' => $branch->id,
        ]);

        $payload = [
            'id' => 'TX-QRIS-TEST-2',
            'total_amount' => 12000,
            'payment_method' => 'QRIS',
            'cash_paid' => 12000,
            'change' => 0,
            'status' => 'Diproses', // Client sends Diproses
            'items' => [
                [
                    'product_id' => 'p_tsel_10_test_2',
                    'product_name' => 'Pulsa Telkomsel 10.000',
                    'product_category' => 'PULSA',
                    'quantity' => 1,
                    'price' => 12000,
                    'customer_phone' => '081234567890',
                    'destination_number' => '081234567890',
                    'operator_name' => 'Telkomsel'
                ]
            ]
        ];

        $response = $this->postJson('/api/transaksi', $payload);
        $response->assertStatus(200);

        // Verify it was stored as 'Sukses' and cache cleared
        $transaction = Transaction::find('TX-QRIS-TEST-2');
        $this->assertNotNull($transaction);
        $this->assertEquals('Sukses', $transaction->status);
        $this->assertFalse(\Illuminate\Support\Facades\Cache::has('payment_success_TX-QRIS-TEST-2'));
    }

    public function test_generate_qrcode_json_format(): void
    {
        $response = $this->postJson('/api/qrcode', [
            'data' => 'https://example.com',
            'json' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
            ])
            ->assertJsonStructure(['success', 'qr_image']);

        $qrImage = $response->json('qr_image');
        $this->assertStringStartsWith('data:image/svg+xml;base64,', $qrImage);
    }

    public function test_generate_qrcode_raw_svg_format(): void
    {
        $response = $this->get('/api/qrcode?data=https://example.com');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/svg+xml');
        $this->assertStringContainsString('<svg', $response->getContent());
        $this->assertStringContainsString('</svg>', $response->getContent());
    }
}
