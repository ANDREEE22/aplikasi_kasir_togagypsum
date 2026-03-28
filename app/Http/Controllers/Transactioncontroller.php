<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Models\TransactionItem;
use App\Models\PaymentProof;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    // ===== CREATE TRANSACTION (Dari Flutter) =====
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            // VALIDATE
            $validated = $request->validate([
                'items' => 'required|array',
                'items.*.name' => 'required|string',
                'items.*.qty' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric',
                'payment_method' => 'required|in:cash,transfer,dp,bayarTempat',
                'shipping_method' => 'required|in:ambil,kirim',
                'shipping_cost' => 'nullable|numeric|min:0',
                'dp_nominal' => 'nullable|numeric|min:0',
            ]);

            // Hitung total_amount dari items (bukan dari Flutter)
            $totalAmount = collect($validated['items'])->sum(fn($item) => $item['price'] * $item['qty']);

            // ===== CREATE TRANSACTION =====
            $transaction = Transaction::create([
                'transaction_code' => 'TRX_' . now()->format('YmdHis'),
                'total_amount' => $totalAmount,
                'shipping_cost' => $validated['shipping_cost'] ?? 0,
                'dp_nominal' => $validated['dp_nominal'] ?? 0,
                'payment_method' => $validated['payment_method'],
                'shipping_method' => $validated['shipping_method'],
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // ===== CREATE TRANSACTION ITEMS =====
            foreach ($validated['items'] as $item) {
                TransactionItem::create([
                    'transaction_id' => $transaction->id,
                    'product_name' => $item['name'],
                    'quantity' => $item['qty'],
                    'price' => $item['price'],
                    'subtotal' => $item['price'] * $item['qty'],
                ]);
            }

            DB::commit();

            Log::info('Transaction created', [
                'transaction_id' => $transaction->id,
                'code' => $transaction->transaction_code,
                'method' => $validated['payment_method'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil disimpan',
                'transaction_id' => $transaction->id,
                'transaction_code' => $transaction->transaction_code,
                'order_id' => $transaction->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction creation error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ===== UPLOAD BUKTI PEMBAYARAN =====
    public function uploadProof(Request $request)
    {
        try {
            // VALIDATE
            $validated = $request->validate([
                'transaction_id' => 'required|exists:transactions,id',
                'payment_method' => 'required|in:transfer,dp',
                'proof_image' => 'required|image|mimes:jpeg,png,jpg|max:10240',
                'description' => 'nullable|string|max:255',
            ]);

            $transaction = Transaction::findOrFail($validated['transaction_id']);

            // ===== SIMPAN FILE =====
            $file = $request->file('proof_image');
            $fileName = 'bukti_' . $validated['payment_method'] . '_' . now()->format('YmdHis') . '.jpg';
            
            $storagePath = Storage::disk('public')->putFileAs(
                'payment_proofs',
                $file,
                $fileName
            );

            if (!$storagePath) {
                return response()->json([
                    'success' => false,
                    'message' => 'Gagal menyimpan file',
                ], 500);
            }

            // ===== CREATE PAYMENT PROOF RECORD =====
            $proof = PaymentProof::create([
                'transaction_id' => $transaction->id,
                'payment_method' => $validated['payment_method'],
                'file_name' => $fileName,
                'file_path' => $storagePath,
                'file_url' => Storage::url($storagePath),
                'file_size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'description' => $validated['description'],
                'uploaded_at' => now(),
            ]);

            Log::info('Payment proof uploaded', [
                'proof_id' => $proof->id,
                'transaction_id' => $transaction->id,
                'method' => $validated['payment_method'],
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Bukti pembayaran berhasil disimpan',
                'proof_id' => $proof->id,
                'file_url' => $proof->file_url,
            ], 201);

        } catch (\Exception $e) {
            Log::error('Proof upload error', [
                'message' => $e->getMessage(),
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ===== GET TRANSACTION DETAIL =====
    public function show($id)
    {
        try {
            $transaction = Transaction::with('items', 'paymentProof')->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $transaction->id,
                    'transaction_code' => $transaction->transaction_code,
                    'total_amount' => $transaction->total_amount,
                    'shipping_cost' => $transaction->shipping_cost,
                    'dp_nominal' => $transaction->dp_nominal,
                    'payment_method' => $transaction->payment_method,
                    'shipping_method' => $transaction->shipping_method,
                    'status' => $transaction->status,
                    'completed_at' => $transaction->completed_at,
                    'items' => $transaction->items->map(fn($item) => [
                        'product_name' => $item->product_name,
                        'quantity' => $item->quantity,
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                    ]),
                    'payment_proof' => $transaction->paymentProof ? [
                        'id' => $transaction->paymentProof->id,
                        'file_url' => $transaction->paymentProof->file_url,
                        'uploaded_at' => $transaction->paymentProof->uploaded_at,
                    ] : null,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Transaksi tidak ditemukan',
            ], 404);
        }
    }

    // ===== GET ALL TRANSACTIONS =====
    public function index()
    {
        try {
            $transactions = Transaction::with('items', 'paymentProof')
                ->orderByDesc('created_at')
                ->paginate(50);

            return response()->json([
                'success' => true,
                'data' => $transactions->map(fn($trx) => [
                    'id' => $trx->id,
                    'transaction_code' => $trx->transaction_code,
                    'total_amount' => $trx->total_amount,
                    'shipping_cost' => $trx->shipping_cost,
                    'dp_nominal' => $trx->dp_nominal,
                    'payment_method' => $trx->payment_method,
                    'shipping_method' => $trx->shipping_method,
                    'has_proof' => $trx->paymentProof ? true : false,
                    'completed_at' => $trx->completed_at,
                    'items' => $trx->items->map(fn($item) => [
                        'product_name' => $item->product_name,
                        'name' => $item->product_name, // alias for Flutter
                        'quantity' => $item->quantity,
                        'qty' => $item->quantity,      // alias for Flutter
                        'price' => $item->price,
                        'subtotal' => $item->subtotal,
                    ]),
                ]),
                'pagination' => [
                    'current_page' => $transactions->currentPage(),
                    'last_page' => $transactions->lastPage(),
                    'total' => $transactions->total(),
                    'per_page' => $transactions->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ===== GET PAYMENT PROOF =====
    public function getProof($transactionId)
    {
        try {
            $proof = PaymentProof::where('transaction_id', $transactionId)->firstOrFail();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $proof->id,
                    'transaction_id' => $proof->transaction_id,
                    'payment_method' => $proof->payment_method,
                    'file_url' => $proof->file_url,
                    'file_name' => $proof->file_name,
                    'file_size' => $proof->file_size,
                    'description' => $proof->description,
                    'uploaded_at' => $proof->uploaded_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Bukti pembayaran tidak ditemukan',
            ], 404);
        }
    }

    // ===== DELETE TRANSACTION =====
    public function destroy($id)
    {
        try {
            $transaction = Transaction::findOrFail($id);

            // Hapus bukti pembayaran jika ada
            if ($transaction->paymentProof && $transaction->paymentProof->file_path) {
                Storage::disk('public')->delete($transaction->paymentProof->file_path);
            }

            // Hapus transaksi (items akan otomatis terhapus karena cascade)
            $transaction->delete();

            Log::info('Transaction deleted', ['id' => $id]);

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil dihapus',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function markLunas(Request $request, $id)
{
    try {
        DB::beginTransaction();
 
        // VALIDATE
        $validated = $request->validate([
            'payment_method' => 'required|in:cash,transfer',
            'final_payment' => 'required|numeric|min:0',
            'status' => 'required|in:lunas',
        ]);
 
        // FIND TRANSACTION
        $transaction = Transaction::findOrFail($id);
 
        // ✅ IMPORTANT: HANYA UPDATE STATUS - JANGAN UBAH ITEMS/STOK
        // Karena stok sudah dikurangi saat transaksi pertama (DP)
        
        $transaction->update([
            'payment_method' => $validated['payment_method'], // Update ke cash/transfer
            'status' => 'lunas', // Status changed from "completed" to "lunas"
            'completed_at' => now(),
        ]);
 
        DB::commit();
 
        Log::info('DP Marked as Lunas', [
            'transaction_id' => $transaction->id,
            'final_payment' => $validated['final_payment'],
            'payment_method' => $validated['payment_method'],
        ]);
 
        return response()->json([
            'success' => true,
            'message' => 'DP Lunas! Transaksi berhasil diupdate',
            'transaction_id' => $transaction->id,
            'status' => 'lunas',
            'payment_method' => $validated['payment_method'],
        ], 200);
 
    } catch (\Illuminate\Validation\ValidationException $e) {
        DB::rollBack();
        return response()->json([
            'success' => false,
            'message' => 'Validasi gagal',
            'errors' => $e->errors(),
        ], 422);
    } catch (\Exception $e) {
        DB::rollBack();
        Log::error('Mark Lunas error', ['message' => $e->getMessage()]);
        return response()->json([
            'success' => false,
            'message' => 'Error: ' . $e->getMessage(),
        ], 500);
    }
}

}

