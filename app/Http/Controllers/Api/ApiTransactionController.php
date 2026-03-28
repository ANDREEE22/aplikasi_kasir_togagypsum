<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;

class ApiTransactionController extends Controller
{
    public function checkout(Request $request)
    {
        // 1. Validasi - TAMBAHKAN customer_name
        $validator = Validator::make($request->all(), [
            'items' => 'required|array',
            'items.*.id' => 'nullable|integer',
            'items.*.qty' => 'required|integer|min:1',
            'items.*.price_type' => 'nullable|in:normal,medium,high',
            'payment_method' => 'required|string',
            'shipping_method' => 'nullable|in:ambil,kirim',
            'shipping_cost' => 'nullable|numeric|min:0',
            'dp_nominal' => 'nullable|numeric|min:0',
            'customer_name' => 'nullable|string|max:255', // ← TAMBAHKAN
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Data validasi gagal', 'errors' => $validator->errors()], 422);
        }

        DB::beginTransaction();
        try {
            $user = Auth::user();
            $items = $request->items;
            $totalBayar = 0;
            $totalNormal = 0;
            $totalMedium = 0;
            $totalHigh = 0;

            // 2. Hitung Total & Cek Stok
            foreach ($items as $item) {
                $type = $item['price_type'] ?? 'normal';

                if (!empty($item['id'])) {
                    $product = Product::find($item['id']);
                    if ($product && $product->stock < $item['qty']) {
                        return response()->json(['message' => "Stok {$product->name} habis/kurang!"], 400);
                    }
                }

                $price = $item['price'] ?? 0;

                switch ($type) {
                    case 'medium': $totalMedium += $price * $item['qty']; break;
                    case 'high':   $totalHigh   += $price * $item['qty']; break;
                    default:       $totalNormal += $price * $item['qty']; break;
                }

                $totalBayar += $price * $item['qty'];
            }

            // 3. Simpan ke Tabel Orders - TAMBAHKAN customer_name
            $order = Order::create([
                'user_id' => $user->id,
                'total' => $totalBayar,
                'total_normal' => $totalNormal,
                'total_medium' => $totalMedium,
                'total_high' => $totalHigh,
                'status' => $request->payment_method == 'dp' ? 'dp' : 'paid',
                'payment_method' => $request->payment_method,
                'recipient_name' => $user->name,
                'shipping_method' => $request->shipping_method ?? 'ambil',
                'shipping_cost' => $request->shipping_cost ?? 0,
                'dp_nominal' => $request->dp_nominal ?? 0,
                'customer_name' => $request->customer_name ?? $user->name, // ← TAMBAHKAN, default ke nama user
            ]);

            // 4. Simpan ke Tabel OrderItems
            foreach ($items as $item) {
                $price    = $item['price'] ?? 0;
                $qty      = $item['qty'] ?? 1;
                $type     = $item['price_type'] ?? 'normal';
                $product  = !empty($item['id']) ? Product::find($item['id']) : null;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $product?->id ?? 0,
                    'custom_id'       => $item['custom_id'] ?? $product?->custom_id,
                    'name'            => $item['name'] ?? $product?->name ?? 'Produk',
                    'category'        => $item['category'] ?? $product?->category,
                    'length_diameter' => $item['length_diameter'] ?? $product?->length_diameter,
                    'width'           => $item['width'] ?? $product?->width,
                    'price_normal'    => $item['price_normal'] ?? $product?->price_normal ?? 0,
                    'price_medium'    => $item['price_medium'] ?? $product?->price_medium ?? 0,
                    'price_high'      => $item['price_high'] ?? $product?->price_high ?? 0,
                    'qty'             => $qty,
                    'price_type'      => $type,
                    'price'           => $price,
                    'subtotal'        => $price * $qty,
                ]);

                if ($product) {
                    $product->decrement('stock', $qty);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi Berhasil!',
                'order_id' => $order->id,
                'total' => $totalBayar
            ], 200);

        } catch (\Exception $e) {
            DB::rollback();
            return response()->json(['message' => 'Error Server: ' . $e->getMessage()], 500);
        }
    }

    public function history(Request $request)
    {
        $user = Auth::user();
        
        $orders = Order::where('user_id', $user->id)
                        ->with('items') 
                        ->orderBy('created_at', 'desc')
                        ->get();

        return response()->json([
            'status' => 'success',
            'data' => $orders
        ]);
    }

    public function markLunas(Request $request, $id)
    {
        try {
            DB::beginTransaction();
 
            $validated = $request->validate([
                'payment_method' => 'required|in:cash,transfer',
                'final_payment' => 'required|numeric|min:0',
                'status' => 'required|in:lunas',
            ]);
 
            $order = Order::findOrFail($id);
 
            if ($order->payment_method != 'dp') {
                return response()->json([
                    'success' => false,
                    'message' => 'Order ini bukan DP'
                ], 400);
            }
 
            $order->update([
                'payment_method' => $validated['payment_method'],
                'status' => 'lunas',
                'updated_at' => now(),
            ]);
 
            DB::commit();
 
            return response()->json([
                'success' => true,
                'message' => 'DP Lunas! Transaksi berhasil diupdate',
                'order_id' => $order->id,
                'status' => 'lunas',
                'payment_method' => $validated['payment_method'],
            ], 200);
 
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}