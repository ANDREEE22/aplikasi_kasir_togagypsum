<?php

namespace App\Http\Controllers;

use App\Models\Lonjaran;
use App\Models\LonjaranItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Controller;


class LonjaranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    // GET /api/lonjaran - Ambil daftar lonjaran (exclude status selesai)
    public function index(Request $request)
    {
        try {
            $lonjaran = Lonjaran::where('user_id', $request->user()->id)
                               ->where('order_status', '!=', 'transaksi_selesai')
                               ->orderBy('entry_date', 'desc')
                               ->with('lonjaranItems')
                               ->get();

            return response()->json([
                'status' => 'success',
                'data' => $lonjaran
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/lonjaran/history - Ambil history (status selesai)
    public function history(Request $request)
    {
        try {
            $lonjaran = Lonjaran::where('user_id', $request->user()->id)
                               ->where('order_status', 'transaksi_selesai')
                               ->orderBy('updated_at', 'desc')
                               ->with('lonjaranItems')
                               ->get();

            return response()->json([
                'status' => 'success',
                'data' => $lonjaran
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    // GET /api/lonjaran/{id} - Ambil detail lonjaran
    public function show($id, Request $request)
    {
        try {
            $lonjaran = Lonjaran::where('id', $id)
                               ->where('user_id', $request->user()->id)
                               ->with('lonjaranItems')
                               ->firstOrFail();

            return response()->json([
                'status' => 'success',
                'data' => $lonjaran
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lonjaran tidak ditemukan'
            ], 404);
        }
    }

    // POST /api/lonjaran - Buat lonjaran baru
    public function store(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:191',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:20',
                'entry_date' => 'required|date',
                'delivery_deadline' => 'required|date|after_or_equal:entry_date',
                'delivery_type' => 'required|in:diambil_sendiri,diantar',
                'items' => 'required|array|min:1',
                'items.*.item_name' => 'required|string',
                'items.*.item_price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'payment_status' => 'required|in:lunas,dp,belum_bayar',
                'order_status' => 'required|in:sudah_bayar_belum_kirim,sudah_kirim_belum_bayar,proses,transaksi_selesai',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            // Hitung total harga
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['item_price'] * $item['quantity'];
                $totalPrice += $subtotal;
            }

            // Buat lonjaran
            $lonjaran = Lonjaran::create([
                'user_id' => $request->user()->id,
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'entry_date' => $request->entry_date,
                'delivery_deadline' => $request->delivery_deadline,
                'delivery_type' => $request->delivery_type,
                'total_price' => $totalPrice,
                'payment_status' => $request->payment_status,
                'order_status' => $request->order_status,
                'notes' => $request->notes,
            ]);

            // Buat items
            foreach ($request->items as $item) {
                LonjaranItem::create([
                    'lonjaran_id' => $lonjaran->id,
                    'item_name' => $item['item_name'],
                    'item_price' => $item['item_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['item_price'] * $item['quantity'],
                ]);
            }

            $lonjaran->load('lonjaranItems');

            return response()->json([
                'status' => 'success',
                'message' => 'Lonjaran berhasil dibuat',
                'data' => $lonjaran
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // PUT /api/lonjaran/{id} - Update lonjaran
    public function update($id, Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'customer_name' => 'required|string|max:191',
                'address' => 'required|string',
                'phone_number' => 'required|string|max:20',
                'entry_date' => 'required|date',
                'delivery_deadline' => 'required|date|after_or_equal:entry_date',
                'delivery_type' => 'required|in:diambil_sendiri,diantar',
                'items' => 'required|array|min:1',
                'items.*.item_name' => 'required|string',
                'items.*.item_price' => 'required|numeric|min:0',
                'items.*.quantity' => 'required|integer|min:1',
                'payment_status' => 'required|in:lunas,dp,belum_bayar',
                'order_status' => 'required|in:sudah_bayar_belum_kirim,sudah_kirim_belum_bayar,proses,transaksi_selesai',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'errors' => $validator->errors()], 422);
            }

            $lonjaran = Lonjaran::where('id', $id)
                               ->where('user_id', $request->user()->id)
                               ->firstOrFail();

            // Hitung total harga
            $totalPrice = 0;
            foreach ($request->items as $item) {
                $subtotal = $item['item_price'] * $item['quantity'];
                $totalPrice += $subtotal;
            }

            // Update lonjaran
            $lonjaran->update([
                'customer_name' => $request->customer_name,
                'address' => $request->address,
                'phone_number' => $request->phone_number,
                'entry_date' => $request->entry_date,
                'delivery_deadline' => $request->delivery_deadline,
                'delivery_type' => $request->delivery_type,
                'total_price' => $totalPrice,
                'payment_status' => $request->payment_status,
                'order_status' => $request->order_status,
                'notes' => $request->notes,
            ]);

            // Hapus items lama
            LonjaranItem::where('lonjaran_id', $lonjaran->id)->delete();

            // Buat items baru
            foreach ($request->items as $item) {
                LonjaranItem::create([
                    'lonjaran_id' => $lonjaran->id,
                    'item_name' => $item['item_name'],
                    'item_price' => $item['item_price'],
                    'quantity' => $item['quantity'],
                    'subtotal' => $item['item_price'] * $item['quantity'],
                ]);
            }

            $lonjaran->load('lonjaranItems');

            return response()->json([
                'status' => 'success',
                'message' => 'Lonjaran berhasil diupdate',
                'data' => $lonjaran
            ]);

        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    // DELETE /api/lonjaran/{id} - Hapus lonjaran
    public function destroy($id, Request $request)
    {
        try {
            $lonjaran = Lonjaran::where('id', $id)
                               ->where('user_id', $request->user()->id)
                               ->firstOrFail();

            $lonjaran->delete();

            return response()->json([
                'status' => 'success',
                'message' => 'Lonjaran berhasil dihapus'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Lonjaran tidak ditemukan'
            ], 404);
        }
    }
}