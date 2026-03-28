<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class AdminProductController extends Controller
{
    // Halaman List Produk
    public function index()
    {
        $products = Product::all();
        return view('products.index', compact('products'));
    }

    // Halaman Form Tambah
    public function create()
    {
        return view('products.create');
    }

    // Proses Simpan ke Database
    public function store(Request $request)
    {
        $request->validate([
            'custom_id' => 'nullable|string',
            'name' => 'required',
            'category' => 'nullable|string',
            'length_diameter' => 'nullable|string',
            'width' => 'nullable|string',
            'price_normal' => 'required|numeric',
            'price_medium' => 'required|numeric',
            'price_high' => 'required|numeric',
            'stock' => 'required|numeric',
        ]);

        Product::create($request->all());

        return redirect()->route('admin.products.index')->with('success', 'Produk berhasil ditambahkan');
    }

    // Form Edit dan Update (Bisa ditambahkan nanti)
}