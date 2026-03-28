<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product; // Pastikan Model ini ada
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // 1. Total Pendapatan (Sum dari total di tabel orders)
        $totalRevenue = Order::sum('total');

        // 2. Total Transaksi (Jumlah baris di tabel orders)
        $totalTransactions = Order::count();

        // 3. Total Item Terjual (Perlu join ke order_items jika mau akurat, atau sum qty)
        // Asumsi ada tabel order_items. Jika belum ada model OrderItem, kita pakai Query Builder saja biar aman.
        $totalItemsSold = DB::table('order_items')->sum('qty');

        // 4. Produk Stok Menipis (Misal: Kurang dari 5)
        $lowStockProducts = Product::where('stock', '<=', 5)
                                   ->orderBy('stock', 'asc')
                                   ->limit(5) // Ambil 5 teratas
                                   ->get();
        
        // Modifikasi data produk low stock agar ada image url
        $lowStockProducts->map(function ($product) {
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => [
                'revenue' => $totalRevenue,
                'transactions' => $totalTransactions,
                'items_sold' => (int) $totalItemsSold,
                'products_count' => Product::count(),
                'low_stock_items' => $lowStockProducts
            ]
        ]);
    }
}