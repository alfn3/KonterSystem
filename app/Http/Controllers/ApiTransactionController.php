<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\CashierReconciliation;
use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\Customer;
use App\Models\Device;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApiTransactionController extends Controller
{
    public function mobileLogin(Request $request)
    {
        $validated = $request->validate([
            'whatsapp' => 'required|string',
        ]);

        $user = \App\Models\User::where('whatsapp', $validated['whatsapp'])->first();

        if ($user) {
            if (!$user->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akun Anda telah dinonaktifkan.'
                ], 403);
            }
            return response()->json([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'whatsapp' => $user->whatsapp,
                ]
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Nomor WhatsApp tidak terdaftar atau tidak aktif.'
        ], 401);
    }

    public function requestOtp(Request $request)
    {
        Log::info('Received requestOtp parameters:', $request->all());
        $validated = $request->validate([
            'agent_id' => 'required|string',
            'whatsapp' => 'required|string',
        ]);

        $user = \App\Models\User::where('agent_id', $validated['agent_id'])
            ->where('whatsapp', $validated['whatsapp'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Agent ID atau nomor WhatsApp tidak cocok atau tidak terdaftar.'
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan.'
            ], 403);
        }

        // Generate 6-digit random OTP code
        $otp = sprintf("%06d", mt_rand(0, 999999));

        // Store OTP in cache for 5 minutes (300 seconds)
        \Illuminate\Support\Facades\Cache::put('otp_' . $validated['whatsapp'], $otp, 300);

        // Send OTP via Fonnte or log it
        $token = env('FONNTE_TOKEN');
        $message = "Kode OTP Sahabat Counter Anda adalah: {$otp}. Berlaku selama 5 menit. Jangan bagikan kode ini kepada siapapun.";

        if ($token) {
            try {
                $response = \Illuminate\Support\Facades\Http::withHeaders([
                    'Authorization' => $token,
                ])->post('https://api.fonnte.com/send', [
                    'target' => $validated['whatsapp'],
                    'message' => $message,
                ]);
                Log::info("Fonnte WA OTP response: " . $response->body());
            } catch (\Exception $e) {
                Log::error("Failed to send WA OTP via Fonnte: " . $e->getMessage());
            }
        } else {
            Log::info("FONNTE_TOKEN not set. Simulated OTP for {$validated['whatsapp']} is: {$otp}");
        }

        $responseBody = [
            'success' => true,
            'message' => 'Kode OTP berhasil dikirim ke WhatsApp Anda.'
        ];

        // If app.debug is true, return the OTP in response body for convenient testing
        if (config('app.debug')) {
            $responseBody['otp'] = $otp;
        }

        return response()->json($responseBody, 200);
    }

    public function verifyOtp(Request $request)
    {
        Log::info('Received verifyOtp parameters:', $request->all());
        $validated = $request->validate([
            'agent_id' => 'required|string',
            'whatsapp' => 'required|string',
            'otp' => 'required|string',
        ]);

        $user = \App\Models\User::where('agent_id', $validated['agent_id'])
            ->where('whatsapp', $validated['whatsapp'])
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Agent ID atau nomor WhatsApp tidak cocok atau tidak terdaftar.'
            ], 404);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan.'
            ], 403);
        }

        $cachedOtp = \Illuminate\Support\Facades\Cache::get('otp_' . $validated['whatsapp']);

        if (!$cachedOtp) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP sudah kadaluwarsa. Silakan minta kode baru.'
            ], 400);
        }

        if ($cachedOtp !== $validated['otp']) {
            return response()->json([
                'success' => false,
                'message' => 'Kode OTP salah.'
            ], 400);
        }

        // OTP verified successfully, check device status
        $deviceId = $request->device_id;
        if ($deviceId) {
            // Deactivate all other devices for this agent ID since this is a new successful login
            Device::where('agent_id', $validated['agent_id'])
                ->where('device_id', '!=', $deviceId)
                ->update(['is_active' => false]);

            $device = Device::where('agent_id', $validated['agent_id'])
                ->where('device_id', $deviceId)
                ->first();

            if ($device) {
                $device->update([
                    'device_name' => $request->device_name ?: $device->device_name,
                    'is_active' => true,
                    'last_active_at' => now(),
                ]);
            } else {
                Device::create([
                    'agent_id' => $validated['agent_id'],
                    'device_id' => $deviceId,
                    'device_name' => $request->device_name ?: 'Device Baru',
                    'is_active' => true,
                    'last_active_at' => now(),
                ]);
            }
        }

        // Clear OTP from cache
        \Illuminate\Support\Facades\Cache::forget('otp_' . $validated['whatsapp']);

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'whatsapp' => $user->whatsapp,
            ]
        ], 200);
    }

    public function checkSession(Request $request)
    {
        $validated = $request->validate([
            'agent_id' => 'required|string',
            'device_id' => 'required|string',
        ]);

        $user = \App\Models\User::where('agent_id', $validated['agent_id'])->first();
        if (!$user || !$user->is_active) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Akun dinonaktifkan atau tidak ditemukan.'
            ], 403);
        }

        $device = Device::where('agent_id', $validated['agent_id'])
            ->where('device_id', $validated['device_id'])
            ->first();

        if (!$device) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Device tidak terdaftar.'
            ], 403);
        }

        if (!$device->is_active) {
            return response()->json([
                'success' => false,
                'valid' => false,
                'message' => 'Device dinonaktifkan.'
            ], 403);
        }

        $device->update(['last_active_at' => now()]);

        return response()->json([
            'success' => true,
            'valid' => true
        ], 200);
    }

    public function registerDevice(Request $request)
    {
        Log::info('Received registerDevice parameters:', $request->all());
        $validated = $request->validate([
            'agent_id' => 'required|string',
            'device_id' => 'required|string',
            'device_name' => 'nullable|string',
        ]);

        $agentId = $validated['agent_id'];
        $deviceId = $validated['device_id'];
        $deviceName = $validated['device_name'] ?: 'Device Baru';

        $device = Device::where('agent_id', $agentId)
            ->where('device_id', $deviceId)
            ->first();

        if ($device) {
            if (!$device->is_active) {
                return response()->json([
                    'success' => false,
                    'valid' => false,
                    'message' => 'Device ini telah dinonaktifkan.'
                ], 403);
            }
            
            $device->update([
                'device_name' => $deviceName,
                'last_active_at' => now(),
            ]);
        } else {
            // If device does not exist, register it.
            // Deactivate all other devices for this agent ID to prevent multi-device bypass
            Device::where('agent_id', $agentId)
                ->update(['is_active' => false]);

            $device = Device::create([
                'agent_id' => $agentId,
                'device_id' => $deviceId,
                'device_name' => $deviceName,
                'is_active' => true,
                'last_active_at' => now(),
            ]);
        }

        return response()->json([
            'success' => true,
            'valid' => true,
            'message' => 'Device berhasil didaftarkan.',
            'device' => $device
        ], 200);
    }

    public function generateQris(Request $request)
    {
        Log::info('Received generateQris request:', $request->all());
        $validated = $request->validate([
            'amount' => 'required|numeric',
            'order_id' => 'required|string',
            'fee_type' => 'nullable|string',
            'fee_value' => 'nullable|numeric',
        ]);

        $apiKey = config('services.temanqris.api_key');
        if (!$apiKey) {
            Log::info('TEMANQRIS_API_KEY not set. Returning mockup QRIS.');
            $mockQris = '00020101021226580009COM.DUMMY123456789012345678905204000053033605405100005802ID5916SAHABAT COUNTER6006BEKASI610517121630489AB';
            
            try {
                $options = new \chillerlan\QRCode\QROptions([
                    'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
                ]);
                $qrImage = (new \chillerlan\QRCode\QRCode($options))->render($mockQris);
            } catch (\Exception $e) {
                Log::error('Mock QR generate error: ' . $e->getMessage());
                $qrImage = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
            }

            // Cache mockup QRIS string
            \Illuminate\Support\Facades\Cache::put('qris_string_' . $validated['order_id'], $mockQris, 3600);

            $totalAmount = $validated['amount'] + ($validated['fee_value'] ?? 0);

            return response()->json([
                'success' => true,
                'qris' => $mockQris,
                'qr_image' => $qrImage,
                'amount' => $totalAmount,
                'expires_at' => now()->addMinutes(15)->toDateTimeString(),
            ], 200);
        }

        // Prepare webhook URL
        $webhookUrl = config('services.temanqris.webhook_url') ?: url('/api/callback/payment');

        try {
            $apiPayload = [
                'amount' => (int)$validated['amount'],
                'order_id' => $validated['order_id'],
                'webhook_url' => $webhookUrl,
            ];
            if (isset($validated['fee_value'])) {
                $apiPayload['fee_value'] = (int)$validated['fee_value'];
                $apiPayload['fee_type'] = $validated['fee_type'] ?: 'rupiah';
            }

            $response = \Illuminate\Support\Facades\Http::withHeaders([
                'X-API-Key' => $apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ])->post('https://temanqris.com/api/qris/generate', $apiPayload);

            Log::info('TemanQRIS API response: ' . $response->body());

            // Write detailed qris debug log
            $logPayload = $apiPayload;
            $logPayload['api_key_masked'] = substr($apiKey, 0, 5) . '...' . substr($apiKey, -5);
            $logData = "[" . date('Y-m-d H:i:s') . "] GENERATE QRIS REQUEST" . PHP_EOL .
                       "Request URL: https://temanqris.com/api/qris/generate" . PHP_EOL .
                       "Request Payload: " . json_encode($logPayload, JSON_PRETTY_PRINT) . PHP_EOL .
                       "Response Status Code: " . $response->status() . PHP_EOL .
                       "Response Body: " . $response->body() . PHP_EOL .
                       "--------------------------------------------------" . PHP_EOL;
            file_put_contents(storage_path('logs/qris_debug.log'), $logData, FILE_APPEND);

            if ($response->successful()) {
                $data = $response->json();
                $qrisString = $data['qris'] ?? '';
                // Cache the real QRIS string
                \Illuminate\Support\Facades\Cache::put('qris_string_' . $validated['order_id'], $qrisString, 3600);

                return response()->json([
                    'success' => true,
                    'qris' => $qrisString,
                    'qr_image' => $data['qr_image'] ?? '',
                    'amount' => $data['amount'] ?? ($validated['amount'] + ($validated['fee_value'] ?? 0)),
                    'expires_at' => $data['expires_at'] ?? '',
                ], 200);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate QRIS dari TemanQRIS: ' . ($response->json('message') ?: $response->body())
                ], $response->status());
            }
        } catch (\Exception $e) {
            $logData = "[" . date('Y-m-d H:i:s') . "] GENERATE QRIS EXCEPTION" . PHP_EOL .
                       "Request Payload: " . json_encode([
                           'amount' => $validated['amount'],
                           'order_id' => $validated['order_id'],
                           'webhook_url' => $webhookUrl,
                       ], JSON_PRETTY_PRINT) . PHP_EOL .
                       "Exception Message: " . $e->getMessage() . PHP_EOL .
                       "Stack Trace: " . $e->getTraceAsString() . PHP_EOL .
                       "--------------------------------------------------" . PHP_EOL;
            file_put_contents(storage_path('logs/qris_debug.log'), $logData, FILE_APPEND);

            Log::error('Error generating QRIS: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    public function generateQrCode(Request $request)
    {
        $validated = $request->validate([
            'data' => 'required|string',
            'json' => 'nullable|boolean',
            'base64' => 'nullable|boolean',
        ]);

        $data = $validated['data'];

        try {
            $options = new \chillerlan\QRCode\QROptions([
                'outputInterface' => \chillerlan\QRCode\Output\QRMarkupSVG::class,
            ]);
            $qrDataUri = (new \chillerlan\QRCode\QRCode($options))->render($data);
            
            // If JSON requested or client wants json response
            if ($request->wantsJson() || $request->input('json') || $request->input('base64')) {
                return response()->json([
                    'success' => true,
                    'qr_image' => $qrDataUri,
                ], 200);
            }

            // Otherwise, decode base64 URI and output raw SVG
            $svgContent = $qrDataUri;
            if (strpos($qrDataUri, 'data:image/svg+xml;base64,') === 0) {
                $svgContent = base64_decode(substr($qrDataUri, strlen('data:image/svg+xml;base64,')));
            }

            return response($svgContent)
                ->header('Content-Type', 'image/svg+xml')
                ->header('Cache-Control', 'public, max-age=31536000');

        } catch (\Exception $e) {
            Log::error('Error generating QR Code: ' . $e->getMessage());
            
            if ($request->wantsJson() || $request->input('json')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal generate QR Code: ' . $e->getMessage(),
                ], 500);
            }

            return response('Error: ' . $e->getMessage(), 500)
                ->header('Content-Type', 'text/plain');
        }
    }

    public function getStatus($id)
    {
        $transaction = Transaction::find($id);

        if ($transaction) {
            return response()->json([
                'success' => true,
                'status' => $transaction->status,
            ], 200);
        }

        // If transaction not created in DB yet, but payment was cached, return Sukses!
        if (\Illuminate\Support\Facades\Cache::has('payment_success_' . $id)) {
            return response()->json([
                'success' => true,
                'status' => 'Sukses',
            ], 200);
        }

        return response()->json([
            'success' => false,
            'message' => 'Transaksi tidak ditemukan.',
        ], 404);
    }

    public function handlePaymentCallback(Request $request)
    {
        Log::info('Received TemanQRIS Webhook:', $request->all());

        // Verify signature
        $signature = $request->header('X-TemanQRIS-Signature');
        $webhookSecret = config('services.temanqris.webhook_secret');

        if ($webhookSecret) {
            $expectedSignature = 'sha256=' . hash_hmac('sha256', $request->getContent(), $webhookSecret);
            if ($signature !== $expectedSignature) {
                Log::warning('TemanQRIS Webhook signature mismatch.');
                return response()->json(['success' => false, 'message' => 'Invalid signature'], 400);
            }
        }

        $orderId = $request->input('data.order_id') ?: $request->input('order_id');
        $status = $request->input('data.status') ?: $request->input('status');
        $event = $request->input('event');

        if ($orderId && ($status === 'paid' || $status === 'confirmed' || $event === 'payment.confirmed')) {
            $transaction = Transaction::where('id', $orderId)->first();

            if ($transaction) {
                if ($transaction->status !== 'Sukses') {
                    DB::transaction(function () use ($transaction) {
                        $transaction->update(['status' => 'Sukses']);
                        
                        $branch = $transaction->branch;
                        if ($branch) {
                            $branch->revenue_mtd += $transaction->total_amount;
                            $branch->save();
                        }
                    });
                }
            } else {
                // If transaction not stored yet (race condition), cache the success status
                \Illuminate\Support\Facades\Cache::put('payment_success_' . $orderId, true, 3600);
            }
        }

        return response()->json(['success' => true], 200);
    }

    public function store(Request $request)
    {
        Log::info('Received transaction from mobile counter:', $request->all());

        $validated = $request->validate([
            'id' => 'required|string',
            'total_amount' => 'required|numeric',
            'payment_method' => 'required|string',
            'cash_paid' => 'required|numeric',
            'change' => 'required|numeric',
            'status' => 'required|string',
            'items' => 'required|array',
            'items.*.product_id' => 'required|string',
            'items.*.product_name' => 'required|string',
            'items.*.product_category' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric',
            'items.*.customer_phone' => 'nullable|string',
            'items.*.destination_number' => 'nullable|string',
            'proof_image' => 'nullable|string',
        ]);

        try {
            DB::beginTransaction();

            $status = $validated['status'];
            if (\Illuminate\Support\Facades\Cache::has('payment_success_' . $validated['id'])) {
                $status = 'Sukses';
                \Illuminate\Support\Facades\Cache::forget('payment_success_' . $validated['id']);
            }

            // Find branch 'mobil1'
            $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
            $this->touchBranchActive($branch);

            if (!$branch) {
                return response()->json(['error' => 'No branch found in system'], 500);
            }

            // Check if transaction has already been processed
            $existingTransaction = Transaction::find($validated['id']);
            $existingMovements = StockMovement::where('reference_no', $validated['id'])->get();

            // Revert previous stock changes if any exist
            if ($existingMovements->isNotEmpty()) {
                foreach ($existingMovements as $move) {
                    if ($move->product_id) {
                        $product = Product::find($move->product_id);
                        if ($product && !$product->is_digital) {
                            $product->final_stock += abs($move->quantity_change);
                            $product->sold_stock -= abs($move->quantity_change);
                            
                            // Recalculate status
                            if ($product->final_stock == 0) {
                                $product->status = 'Habis';
                            } elseif ($product->final_stock <= 5) {
                                $product->status = 'Kritis';
                            } elseif ($product->final_stock <= 10) {
                                $product->status = 'Tipis';
                            } else {
                                $product->status = 'Aman';
                            }
                            $product->save();
                        }
                    }
                    $move->delete();
                }
            }

            if ($existingTransaction) {
                // Find and revert previous electric balance deduction if any
                $existingDigitalHppTotal = 0;
                foreach ($existingTransaction->items as $item) {
                    $product = $item->product;
                    if ($product && $product->is_digital) {
                        $existingDigitalHppTotal += $product->hpp * $item->quantity;
                    }
                }

                if ($existingDigitalHppTotal > 0) {
                    $branch->saldo_elektrik += $existingDigitalHppTotal;
                    $branch->save();
                }

                // Revert branch metrics
                $branch->revenue_mtd = max(0, $branch->revenue_mtd - $existingTransaction->total_amount);
                $existingTransaction->items()->delete();
                $existingTransaction->delete();
            }

            // If the transaction status is Gagal, we return success early without keeping active stock levels changed
            if ($status === 'Gagal') {
                $changeText = 'Pas';
                if ($validated['change'] > 0) {
                    $changeText = 'Kembali: Rp ' . number_format($validated['change'], 0, ',', '.');
                }

                // Log the failed transaction header
                Transaction::create([
                    'id' => $validated['id'],
                    'branch_id' => $branch->id,
                    'total_amount' => $validated['total_amount'],
                    'payment_method' => $validated['payment_method'],
                    'cash_paid' => $validated['cash_paid'],
                    'change' => $validated['change'],
                    'payment_change' => $changeText,
                    'status' => 'Gagal',
                    'customer_id' => 'Pelanggan Gagal',
                    'customer_phone' => $validated['items'][0]['customer_phone'] ?? null,
                    'operator' => 'Andini (Kasir)',
                ]);

                // Recalculate and save branch metrics
                $branchProducts = Product::where('branch_id', $branch->id)->get();
                $branch->stock_available = $branchProducts->where('is_digital', false)->sum('final_stock');
                
                $totalInitialIncoming = $branchProducts->where('is_digital', false)->sum(fn($p) => ($p->initial_stock ?? 0) + ($p->incoming_stock ?? 0));
                $totalFinal = $branchProducts->where('is_digital', false)->sum('final_stock');
                $healthPct = $totalInitialIncoming > 0 ? ($totalFinal / $totalInitialIncoming) * 100 : 100;
                $branch->stock_health = round($healthPct);
                $branch->save();

                DB::commit();
                return response()->json(['success' => true, 'message' => 'Transaction failed, recorded as failed and stock changes skipped'], 200);
            }

            // Calculate total digital amount
            $digitalTotal = 0;
            foreach ($validated['items'] as $item) {
                $sku = $item['product_id'];
                $qty = $item['quantity'];
                
                $product = Product::where('sku', $sku)->where('branch_id', $branch->id)->first();
                $isDigital = false;
                if ($product) {
                    $isDigital = $product->is_digital;
                } else {
                    $category = strtoupper($item['product_category']);
                    if ($category === 'PERDANA') {
                        $category = 'KARTU_PERDANA';
                    }
                    $isDigital = in_array($category, ['PULSA', 'PAKET_DATA', 'E_WALLET', 'GAME', 'TAGIHAN', 'TRANSFER']) ||
                                 ($item['product_category'] === 'Digital');
                }
                
                if ($isDigital) {
                    $digitalTotal += $item['price'] * $qty;
                }
            }

            // We only deduct from branch electric balance inside the loop using HPP, so no retail price deduction is needed here.

            // Generate customer ID
            $todayCount = Transaction::where('branch_id', $branch->id)
                ->whereDate('created_at', now()->toDateString())
                ->count();
            $customerId = 'Pelanggan ' . ($todayCount + 1);

            // Format payment change
            $changeText = 'Pas';
            if ($validated['change'] > 0) {
                $changeText = 'Kembali: Rp ' . number_format($validated['change'], 0, ',', '.');
            } elseif ($validated['cash_paid'] < $validated['total_amount']) {
                $changeText = 'Kurang: Rp ' . number_format($validated['total_amount'] - $validated['cash_paid'], 0, ',', '.');
            }

            // Retrieve cached QRIS data if applicable
            $qrisString = null;
            if ($validated['payment_method'] === 'QRIS') {
                $qrisString = \Illuminate\Support\Facades\Cache::get('qris_string_' . $validated['id']);
                if (!$qrisString && $existingTransaction) {
                    $qrisString = $existingTransaction->qris;
                }
            }

            // Save Base64 proof image if provided
            $proofImagePath = null;
            if (!empty($validated['proof_image'])) {
                try {
                    $imageData = $validated['proof_image'];
                    if (preg_match('/^data:image\/(\w+);base64,/', $imageData, $type)) {
                        $imageData = substr($imageData, strpos($imageData, ',') + 1);
                        $extension = strtolower($type[1]);
                    } else {
                        $extension = 'jpg';
                    }
                    $imageData = base64_decode($imageData);
                    if ($imageData !== false) {
                        $fileName = 'proof_' . $validated['id'] . '_' . time() . '.' . $extension;
                        $path = 'proofs/' . $fileName;
                        \Illuminate\Support\Facades\Storage::disk('public')->put($path, $imageData);
                        $proofImagePath = 'storage/' . $path;
                    }
                } catch (\Exception $e) {
                    Log::error('Failed to save proof image: ' . $e->getMessage());
                }
            }

            // Create Transaction header
            $transaction = Transaction::create([
                'id' => $validated['id'],
                'branch_id' => $branch->id,
                'total_amount' => $validated['total_amount'],
                'payment_method' => $validated['payment_method'],
                'cash_paid' => $validated['cash_paid'],
                'change' => $validated['change'],
                'payment_change' => $changeText,
                'qris' => $qrisString,
                'status' => $status,
                'proof_image' => $proofImagePath ?: ($existingTransaction ? $existingTransaction->proof_image : null),
                'customer_id' => $customerId,
                'customer_phone' => $validated['items'][0]['customer_phone'] ?? null,
                'operator' => 'Andini (Kasir)',
                'saldo_elektrik_remaining' => $branch->saldo_elektrik,
            ]);

            // Process items
            foreach ($validated['items'] as $item) {
                $sku = $item['product_id']; // Using product_id as SKU from Android App
                $qty = $item['quantity'];

                // Find product by SKU for this branch
                $product = Product::where('sku', $sku)->where('branch_id', $branch->id)->first();

                if (!$product) {
                    // Check if it should be marked as digital based on name/category
                    $category = strtoupper($item['product_category']);
                    if ($category === 'PERDANA') {
                        $category = 'KARTU_PERDANA';
                    }
                    $isDigital = in_array($category, ['PULSA', 'PAKET_DATA', 'E_WALLET', 'GAME', 'TAGIHAN', 'TRANSFER']) ||
                                 ($item['product_category'] === 'Digital');

                    // Try to clone from Gudang
                    $gudangProduct = Product::where('sku', $sku)->whereNull('branch_id')->first();
                    if ($gudangProduct) {
                        $product = Product::create([
                            'brand' => $gudangProduct->brand,
                            'name' => $gudangProduct->name,
                            'sku' => $sku,
                            'category' => $gudangProduct->category,
                            'is_digital' => $gudangProduct->is_digital ?? false,
                            'initial_stock' => ($gudangProduct->is_digital ?? false) ? null : 100,
                            'incoming_stock' => ($gudangProduct->is_digital ?? false) ? null : 0,
                            'final_stock' => ($gudangProduct->is_digital ?? false) ? null : 100,
                            'sold_stock' => ($gudangProduct->is_digital ?? false) ? null : 0,
                            'price' => $item['price'],
                            'hpp' => $gudangProduct->hpp,
                            'status' => ($gudangProduct->is_digital ?? false) ? null : 'Aman',
                            'branch_id' => $branch->id,
                        ]);
                    } else {
                        // Create a new product dynamically in Gudang and all branches
                        $gudangProduct = Product::create([
                            'brand' => $item['operator_name'] ?? 'Generic',
                            'name' => $item['product_name'],
                            'sku' => $sku,
                            'category' => $item['product_category'],
                            'is_digital' => $isDigital,
                            'initial_stock' => $isDigital ? null : 0,
                            'incoming_stock' => $isDigital ? null : 0,
                            'final_stock' => $isDigital ? null : 0,
                            'sold_stock' => $isDigital ? null : 0,
                            'price' => $item['price'],
                            'hpp' => $item['price'] * 0.9, // Estimate HPP as 90% of price
                            'status' => $isDigital ? null : 'Habis',
                            'branch_id' => null,
                        ]);

                        $allBranches = Branch::all();
                        foreach ($allBranches as $b) {
                            $isCurrentBranch = ($b->id === $branch->id);
                            Product::create([
                                'brand' => $gudangProduct->brand,
                                'name' => $gudangProduct->name,
                                'sku' => $sku,
                                'category' => $gudangProduct->category,
                                'is_digital' => $isDigital,
                                'initial_stock' => $isDigital ? null : ($isCurrentBranch ? 100 : 0),
                                'incoming_stock' => $isDigital ? null : 0,
                                'final_stock' => $isDigital ? null : ($isCurrentBranch ? 100 : 0),
                                'sold_stock' => $isDigital ? null : 0,
                                'price' => $gudangProduct->price,
                                'hpp' => $gudangProduct->hpp,
                                'status' => $isDigital ? null : ($isCurrentBranch ? 'Aman' : 'Habis'),
                                'branch_id' => $b->id,
                            ]);
                        }

                        $product = Product::where('sku', $sku)->where('branch_id', $branch->id)->first();
                    }
                }

                // Create TransactionItem
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_id' => $product->id,
                    'product_sku' => $product->sku,
                    'product_name' => $product->name,
                    'product_category' => $product->category,
                    'quantity' => $qty,
                    'price' => $item['price'],
                    'destination_number' => $item['destination_number'] ?? null,
                ]);

                // Update stock and log movement ONLY for physical products
                if (!$product->is_digital) {
                    $product->final_stock -= $qty;
                    $product->sold_stock += $qty;
                    
                    // Determine status
                    if ($product->final_stock == 0) {
                        $product->status = 'Habis';
                    } elseif ($product->final_stock <= 5) {
                        $product->status = 'Kritis';
                    } elseif ($product->final_stock <= 10) {
                        $product->status = 'Tipis';
                    } else {
                        $product->status = 'Aman';
                    }
                    $product->save();

                    // Log stock movement
                    StockMovement::create([
                        'product_id' => $product->id,
                        'product_name' => $product->name,
                        'product_sku' => $product->sku,
                        'product_category' => $product->category,
                        'branch_name' => $branch->name,
                        'quantity_change' => -$qty,
                        'final_stock' => $product->final_stock,
                        'type' => 'Penjualan',
                        'customer_phone' => $item['customer_phone'] ?? null,
                        'customer_id' => $customerId,
                        'destination_number' => null,
                        'payment_method' => $validated['payment_method'],
                        'payment_change' => $changeText,
                        'reference_no' => $validated['id'],
                        'operator' => 'Andini (Kasir)',
                        'saldo_elektrik_remaining' => $branch->saldo_elektrik,
                    ]);
                } else {
                    $branch->saldo_elektrik = max(0, $branch->saldo_elektrik - ($product->hpp * $qty));
                }
            }

            // Save remaining electric balance to transaction
            $transaction->saldo_elektrik_remaining = $branch->saldo_elektrik;
            $transaction->save();

            // Update branch metrics only if transaction is Sukses
            if ($status === 'Sukses') {
                $branch->revenue_mtd += $validated['total_amount'];
            }
            
            // Calculate stock available and stock health
            $branchProducts = Product::where('branch_id', $branch->id)->get();
            $branch->stock_available = $branchProducts->where('is_digital', false)->sum('final_stock');
            
            $totalInitialIncoming = $branchProducts->where('is_digital', false)->sum(fn($p) => ($p->initial_stock ?? 0) + ($p->incoming_stock ?? 0));
            $totalFinal = $branchProducts->where('is_digital', false)->sum('final_stock');
            $healthPct = $totalInitialIncoming > 0 ? ($totalFinal / $totalInitialIncoming) * 100 : 100;
            $branch->stock_health = round($healthPct);
            $branch->save();

            DB::commit();
            return response()->json(['success' => true, 'message' => 'Transaction successfully saved to ' . $branch->name], 200);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed saving transaction: ' . $e->getMessage());
            return response()->json(['error' => 'Failed to save transaction: ' . $e->getMessage()], 500);
        }
    }

    public function index()
    {
        // Find branch 'mobil1'
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json([], 200);
        }

        // Fetch all transactions from the transactions table
        $dbTransactions = Transaction::with('items')
            ->where('branch_id', $branch->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $normalizePhone = function($phone) {
            $digits = preg_replace('/\D/', '', $phone);
            if (strpos($digits, '62') === 0) {
                $digits = '0' . substr($digits, 2);
            }
            return $digits;
        };

        $customers = Customer::all();
        $customersMap = [];
        foreach ($customers as $c) {
            $cleanPhone = $normalizePhone($c->phone);
            if ($cleanPhone) {
                $customersMap[$cleanPhone] = $c->name;
            }
        }

        $transactions = [];

        foreach ($dbTransactions as $trx) {
            $items = [];
            $cleanTrxPhone = $normalizePhone($trx->customer_phone);
            $customerName = $customersMap[$cleanTrxPhone] ?? 'Pelanggan';

            foreach ($trx->items as $item) {
                // Find product to get details
                $product = Product::where('sku', $item->product_sku)->first();
                $operatorName = $product ? $product->brand : 'Generic';

                $items[] = [
                    'product' => [
                        'id' => $item->product_sku,
                        'name' => $item->product_name,
                        'price' => (double)$item->price,
                        'category' => $item->product_category === 'Digital' ? 'PULSA' : strtoupper($item->product_category),
                        'operatorName' => $operatorName,
                        'description' => '',
                        'stock' => null
                    ],
                    'quantity' => $item->quantity,
                    'phoneNumber' => $trx->customer_phone,
                    'customerName' => $customerName,
                    'status' => strtolower($trx->status) === 'sukses' ? 'sukses' : 'gagal',
                    'recipientName' => $item->destination_number
                ];
            }

            // Calculate change from payment_change (e.g. "Kembali: Rp 5.000")
            $change = (double)$trx->change;

            $transactions[] = [
                'id' => $trx->id,
                'items' => $items,
                'totalAmount' => (double)$trx->total_amount,
                'paymentMethod' => $trx->payment_method,
                'cashPaid' => (double)$trx->cash_paid,
                'change' => $change,
                'status' => $trx->status,
                'serialNumber' => $trx->id,
                'timestamp' => $trx->created_at->timestamp * 1000,
                'queueName' => $trx->customer_id ?? 'Pelanggan'
            ];
        }

        return response()->json($transactions, 200);
    }

    public function products()
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json([], 200);
        }

        $dbProducts = Product::where('branch_id', $branch->id)->get();

        $products = [];
        foreach ($dbProducts as $p) {
            $category = strtoupper($p->category);
            if ($category === 'PERDANA') {
                $category = 'KARTU_PERDANA';
            }

            $isDigital = $p->is_digital ||
                         in_array($category, ['PULSA', 'PAKET_DATA', 'E_WALLET', 'GAME', 'TAGIHAN', 'TRANSFER']) ||
                         ($category === 'VOUCHER' && strtoupper($p->brand) === 'PLN') ||
                         ($p->category === 'Digital');

            $products[] = [
                'id' => $p->sku,
                'name' => $p->name,
                'price' => (double)$p->price,
                'category' => $category,
                'operatorName' => $p->brand,
                'description' => $p->brand . ' product',
                'stock' => $isDigital ? null : (int)$p->final_stock
            ];
        }

        return response()->json($products, 200);
    }

    public function getExpenses()
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json([], 200);
        }

        $dbExpenses = Expense::where('branch_id', $branch->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $expenses = [];
        foreach ($dbExpenses as $e) {
            $expenses[] = [
                'id' => 'EX-' . $e->id,
                'category' => $e->category,
                'amount' => (double)$e->amount,
                'description' => $e->description ?? '',
                'photo_path' => $e->photo_path ? url('storage/' . $e->photo_path) : null,
                'timestamp' => $e->created_at->timestamp * 1000
            ];
        }

        return response()->json($expenses, 200);
    }

    public function storeExpense(Request $request)
    {
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric',
            'description' => 'required|string',
            'photo_base64' => 'nullable|string',
        ]);

        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json(['error' => 'No branch found'], 500);
        }

        $photoPath = null;
        if (!empty($validated['photo_base64'])) {
            $imageParts = explode(";base64,", $validated['photo_base64']);
            if (count($imageParts) == 2) {
                $imageTypeAux = explode("image/", $imageParts[0]);
                $imageType = count($imageTypeAux) > 1 ? $imageTypeAux[1] : 'png';
                $imageBase64 = base64_decode($imageParts[1]);
                
                $fileName = 'proofs/' . uniqid() . '.' . $imageType;
                \Illuminate\Support\Facades\Storage::disk('public')->put($fileName, $imageBase64);
                $photoPath = $fileName;
            }
        }

        $expense = Expense::create([
            'branch_id' => $branch->id,
            'category' => $validated['category'],
            'amount' => $validated['amount'],
            'description' => $validated['description'],
            'photo_path' => $photoPath,
        ]);

        if (strtolower($validated['category']) === 'setor saldo') {
            $branch->saldo_elektrik += $validated['amount'];
            $branch->save();
        }

        return response()->json([
            'id' => 'EX-' . $expense->id,
            'category' => $expense->category,
            'amount' => (double)$expense->amount,
            'description' => $expense->description,
            'photo_path' => $expense->photo_path ? url('storage/' . $expense->photo_path) : null,
            'timestamp' => $expense->created_at->timestamp * 1000
        ], 200);
    }

    public function updateExpense(Request $request, $id)
    {
        $cleanId = str_replace('EX-', '', $id);
        
        $validated = $request->validate([
            'category' => 'required|string',
            'amount' => 'required|numeric',
            'description' => 'required|string',
        ]);

        $expense = Expense::find($cleanId);
        if ($expense) {
            $branch = Branch::find($expense->branch_id);
            $this->touchBranchActive($branch);
            // Revert old "setor saldo" if applicable
            if ($branch && strtolower($expense->category) === 'setor saldo') {
                $branch->saldo_elektrik = max(0, $branch->saldo_elektrik - $expense->amount);
            }
            
            // Update
            $expense->update([
                'category' => $validated['category'],
                'amount' => $validated['amount'],
                'description' => $validated['description'],
            ]);

            // Apply new "setor saldo" if applicable
            if ($branch && strtolower($validated['category']) === 'setor saldo') {
                $branch->saldo_elektrik += $validated['amount'];
                $branch->save();
            } else if ($branch) {
                $branch->save();
            }

            return response()->json([
                'id' => 'EX-' . $expense->id,
                'category' => $expense->category,
                'amount' => (double)$expense->amount,
                'description' => $expense->description,
                'timestamp' => $expense->created_at->timestamp * 1000
            ], 200);
        }

        return response()->json(['error' => 'Expense not found'], 404);
    }

    public function deleteExpense($id)
    {
        $cleanId = str_replace('EX-', '', $id);
        
        $expense = Expense::find($cleanId);
        if ($expense) {
            $branch = Branch::find($expense->branch_id);
            $this->touchBranchActive($branch);
            if ($branch && strtolower($expense->category) === 'setor saldo') {
                $branch->saldo_elektrik = max(0, $branch->saldo_elektrik - $expense->amount);
                $branch->save();
            }
            $expense->delete();
            return response()->json(['success' => true, 'message' => 'Expense deleted successfully'], 200);
        }
        
        return response()->json(['error' => 'Expense not found'], 404);
    }

    public function storeClosing(Request $request)
    {
        $validated = $request->validate([
            'sales' => 'required|numeric',
            'gap' => 'required|numeric',
            'name' => 'nullable|string',
            'shift' => 'nullable|string',
            'bon' => 'nullable|numeric',
            'incentive' => 'nullable|numeric',
        ]);

        $gap = $validated['gap'];
        $status = 'Matching';
        if ($gap < 0) {
            $status = 'Discrepancy';
        } elseif ($gap > 0) {
            $status = 'Surplus';
        }

        $recon = CashierReconciliation::create([
            'name' => $validated['name'] ?? 'Andini (Kasir)',
            'shift' => $validated['shift'] ?? 'Shift 1',
            'sales' => $validated['sales'],
            'gap' => $gap,
            'bon' => $validated['bon'] ?? 0,
            'incentive' => $validated['incentive'] ?? 0,
            'status' => $status,
        ]);

        // Update branch 'mobil1' cash status in MySQL
        $branch = Branch::where('name', 'mobil1')->first();
        $this->touchBranchActive($branch);
        if ($branch) {
            if ($gap < 0) {
                $formattedGap = number_format(abs($gap), 0, ',', '.');
                $branch->update([
                    'cash_status' => "- Rp{$formattedGap}",
                    'cash_matched' => false,
                ]);
            } else {
                $branch->update([
                    'cash_status' => 'Cocok',
                    'cash_matched' => true,
                ]);
            }
        }

        return response()->json($recon, 200);
    }

    public function getClosing()
    {
        $branch = Branch::where('name', 'mobil1')->first();
        $this->touchBranchActive($branch);

        $closing = CashierReconciliation::where('name', 'Andini (Kasir)')
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($closing) {
            return response()->json([
                'exists' => true,
                'closing' => [
                    'id' => $closing->id,
                    'name' => $closing->name,
                    'shift' => $closing->shift,
                    'sales' => (double)$closing->sales,
                    'gap' => (double)$closing->gap,
                    'bon' => (double)$closing->bon,
                    'incentive' => (double)$closing->incentive,
                    'status' => $closing->status,
                ]
            ], 200);
        }

        return response()->json([
            'exists' => false
        ], 200);
    }

    public function getSaldoElektrik()
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);
        return response()->json([
            'saldo_elektrik' => $branch ? (double)$branch->saldo_elektrik : 0.0
        ], 200);
    }

    public function storeAttendance(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'branch_name' => 'required|string|max:100',
        ]);

        $branch = Branch::where('name', $validated['branch_name'])->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json(['error' => 'No branch found'], 500);
        }

        $attendance = \App\Models\Attendance::where('branch_id', $branch->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if (!$attendance) {
            $attendance = \App\Models\Attendance::create([
                'branch_id' => $branch->id,
                'name' => $validated['name'],
            ]);
        }

        return response()->json([
            'success' => true,
            'time' => $attendance->created_at->format('H:i'),
        ], 200);
    }

    public function getAttendance()
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json(['exists' => false], 200);
        }

        $attendance = \App\Models\Attendance::where('branch_id', $branch->id)
            ->whereDate('created_at', now()->toDateString())
            ->first();

        if ($attendance) {
            return response()->json([
                'exists' => true,
                'time' => $attendance->created_at->format('H:i'),
            ], 200);
        }

        return response()->json([
            'exists' => false,
        ], 200);
    }

    public function getCustomers()
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $this->touchBranchActive($branch);

        if (!$branch) {
            return response()->json([], 200);
        }

        $customers = Customer::where(function($q) use ($branch) {
                $q->where('branch_id', $branch->id)
                  ->orWhereNull('branch_id');
            })->get();

        $customers->each(function($customer) {
            $customer->total_transactions = Transaction::where('customer_phone', $customer->phone)->count();
        });

        $sortedCustomers = $customers->sortByDesc('total_transactions')->values();

        $result = [];
        foreach ($sortedCustomers as $c) {
            $result[] = [
                'id' => $c->id,
                'phone' => $c->phone,
                'name' => $c->name,
                'total_transactions' => $c->total_transactions,
                'service_type' => $c->service_type
            ];
        }

        return response()->json($result, 200);
    }

    public function storeCustomer(Request $request)
    {
        $branch = Branch::where('name', 'mobil1')->first() ?: Branch::first();
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30|unique:customers,phone',
            'service_type' => 'nullable|string|max:50',
        ]);
        if ($branch) {
            $validated['branch_id'] = $branch->id;
        }
        $customer = Customer::create($validated);
        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'service_type' => $customer->service_type
        ], 201);
    }

    public function updateCustomer(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);
        $validated = $request->validate([
            'name' => 'required|string|max:100',
            'phone' => 'required|string|max:30|unique:customers,phone,' . $id,
            'service_type' => 'nullable|string|max:50',
        ]);
        $customer->update($validated);
        return response()->json([
            'success' => true,
            'id' => $customer->id,
            'name' => $customer->name,
            'phone' => $customer->phone,
            'service_type' => $customer->service_type
        ], 200);
    }

    public function deleteCustomer($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();
        return response()->json(['success' => true], 200);
    }

    private function touchBranchActive($branch)
    {
        if ($branch) {
            $branch->update(['last_active_at' => now()]);
        }
    }

    public function getBranchStatuses()
    {
        $branches = Branch::all();
        $data = $branches->map(function($b) {
            $statusClass = 'bg-slate-400';
            if ($b->status === 'Online' || $b->status === 'Open') {
                $statusClass = 'bg-green-500';
            }

            // Customer count (registered at branch or transacted at branch)
            $customerCount = Customer::where('branch_id', $b->id)
                ->orWhereIn('phone', function($q) use ($b) {
                    $q->select('customer_phone')
                      ->from('transactions')
                      ->where('branch_id', $b->id)
                      ->where('status', 'Sukses')
                      ->whereNotNull('customer_phone');
                })->count();

            // Today's customer count
            $todayCustomerCount = Transaction::where('branch_id', $b->id)
                ->where('status', 'Sukses')
                ->whereDate('created_at', now()->toDateString())
                ->distinct('customer_phone')
                ->count('customer_phone');

            if ($todayCustomerCount === 0) {
                $todayCustomerCount = Transaction::where('branch_id', $b->id)
                    ->where('status', 'Sukses')
                    ->whereDate('created_at', now()->toDateString())
                    ->count();
            }

            // Today's revenue
            $todayRevenue = Transaction::where('branch_id', $b->id)
                ->whereDate('created_at', now()->toDateString())
                ->where('status', 'Sukses')
                ->sum('total_amount');

            // Fallback to revenue_mtd if 0
            $revenueVal = $todayRevenue > 0 ? (double)$todayRevenue : (double)$b->revenue_mtd;

            return [
                'id' => $b->id,
                'name' => $b->name,
                'status' => $b->status,
                'status_class' => $statusClass,
                'last_active_at' => $b->last_active_at ? $b->last_active_at->toDateTimeString() : null,
                'customer_count' => $customerCount,
                'today_customer_count' => $todayCustomerCount,
                'revenue_today' => 'Rp ' . number_format($revenueVal, 0, ',', '.'),
                'saldo_elektrik' => 'Rp ' . number_format((double)$b->saldo_elektrik, 0, ',', '.'),
                'saldo_elektrik_val' => (double)$b->saldo_elektrik,
            ];
        });
        return response()->json($data, 200);
    }
}
