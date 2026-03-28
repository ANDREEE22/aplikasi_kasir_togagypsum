<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index()
    {
        $products = Product::orderBy('created_at', 'desc')->get();
        
        $products->map(function ($product) {
            $product->image_url = $product->image ? asset('storage/' . $product->image) : null;
            return $product;
        });

        return response()->json([
            'status' => 'success',
            'data' => $products
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'custom_id'    => 'nullable|unique:products,custom_id', // ID custom harus unik
            'name'         => 'required',
            'price_normal' => 'required|numeric', // Validasi harga baru
            'price_medium' => 'required|numeric',
            'price_high'   => 'required|numeric',
            'stock'        => 'required|numeric',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product = Product::create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil ditambahkan',
            'data' => $product
        ]);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Not found'], 404);

        $request->validate([
            'custom_id'    => 'nullable|unique:products,custom_id,' . $id, // Unik kecuali milik sendiri
            'name'         => 'required',
            'price_normal' => 'required|numeric',
            'price_medium' => 'required|numeric',
            'price_high'   => 'required|numeric',
            'stock'        => 'required|numeric',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $data = $request->all();

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $path = $request->file('image')->store('products', 'public');
            $data['image'] = $path;
        }

        $product->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Produk berhasil diupdate',
            'data' => $product
        ]);
    }

    public function destroy($id)
    {
        $product = Product::find($id);
        if (!$product) return response()->json(['message' => 'Not found'], 404);

        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        $product->delete();
        return response()->json(['message' => 'Deleted']);
    }
}