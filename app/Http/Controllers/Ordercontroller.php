<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    // ===== POST /checkout =====
    public function store(Request $request)
    {
        try {
            DB::beginTransaction();

            $validated = $request->validate([
                'items'           => 'required|array',
                'items.*.id'      => 'nullable|integer',
                'items.*.name'    => 'required|string',
                'items.*.qty'     => 'required|integer|min:1',
                'items.*.price'   => 'required|numeric',
                'payment_method'  => 'required|string',
                'shipping_method' => 'required|in:ambil,kirim',
                'shipping_cost'   => 'nullable|numeric|min:0',
                'dp_nominal'      => 'nullable|numeric|min:0',
            ]);

            // Hitung total barang dari items
            $totalBarang = collect($validated['items'])
                ->sum(fn($item) => $item['price'] * $item['qty']);

            $shippingCost   = (int) ($validated['shipping_cost'] ?? 0);
            $shippingMethod = $validated['shipping_method'];

            // ===== BUAT ORDER =====
            $order = Order::create([
                'user_id'         => $request->user()->id,
                'total'           => $totalBarang,      // total barang saja
                'total_normal'    => 0,
                'total_medium'    => 0,
                'total_high'      => 0,
                'status'          => 'paid',
                'payment_method'  => $validated['payment_method'],
                'shipping_method' => $shippingMethod,
                'shipping_cost'   => $shippingCost,
                'recipient_name'  => $request->user()->name ?? 'Customer',
            ]);

            // ===== BUAT ORDER ITEMS =====
            foreach ($validated['items'] as $item) {
                $price    = (int) $item['price'];
                $qty      = (int) $item['qty'];
                $subtotal = $price * $qty;

                OrderItem::create([
                    'order_id'        => $order->id,
                    'product_id'      => $item['id'] ?? 0,
                    'custom_id'       => $item['custom_id'] ?? null,
                    'name'            => $item['name'],
                    'category'        => $item['category'] ?? null,
                    'length_diameter' => $item['length_diameter'] ?? null,
                    'width'           => $item['width'] ?? null,
                    'price_normal'    => (int) ($item['price_normal'] ?? 0),
                    'price_medium'    => (int) ($item['price_medium'] ?? 0),
                    'price_high'      => (int) ($item['price_high'] ?? 0),
                    'qty'             => $qty,
                    'price_type'      => $item['price_type'] ?? 'normal',
                    'price'           => $price,
                    'subtotal'        => $subtotal,
                ]);
            }

            DB::commit();

            Log::info('Order created', [
                'order_id'       => $order->id,
                'total'          => $totalBarang,
                'shipping_cost'  => $shippingCost,
                'payment_method' => $validated['payment_method'],
            ]);

            return response()->json([
                'success'  => true,
                'message'  => 'Transaksi berhasil',
                'order_id' => $order->id,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }

    // ===== GET /history =====
    public function history(Request $request)
    {
        try {
            $orders = Order::with('items')
                ->where('user_id', $request->user()->id)
                ->orderByDesc('created_at')
                ->get();

            $data = $orders->map(fn($order) => [
                'id'              => $order->id,
                'total'           => $order->total,           // total barang
                'shipping_cost'   => $order->shipping_cost,   // ✅ ongkir
                'shipping_method' => $order->shipping_method, // ✅ metode kirim
                'payment_method'  => $order->payment_method,
                'status'          => $order->status,
                'created_at'      => $order->created_at,
                'items'           => $order->items->map(fn($item) => [
                    'name'         => $item->name,       // ✅ Flutter baca 'name'
                    'qty'          => $item->qty,        // ✅ Flutter baca 'qty'
                    'price'        => $item->price,
                    'price_type'   => $item->price_type,
                    'price_normal' => $item->price_normal,
                    'price_medium' => $item->price_medium,
                    'price_high'   => $item->price_high,
                    'subtotal'     => $item->subtotal,
                ]),
            ]);

            return response()->json([
                'success' => true,
                'data'    => $data,
            ]);

        } catch (\Exception $e) {
            Log::error('History error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
            ], 500);
        }
    }
}