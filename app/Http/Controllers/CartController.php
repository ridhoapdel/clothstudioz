<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class CartController extends Controller
{
    public function index()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/users/login');
        }

        $user_id = session('user_id');

        // Ambil data keranjang
        $query = "SELECT k.*, p.nama_produk, p.harga, p.gambar_produk, p.stok, 
                         bd.harga_diskon, bd.mulai_diskon, bd.selesai_diskon
                  FROM keranjang k
                  JOIN produk p ON k.produk_id = p.produk_id
                  LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                      AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                  WHERE k.user_id = ?";
        
        $cart_items = DB::select($query, [$user_id]);
        
        return view('keranjang', compact('cart_items'));
    }

    public function wishlist()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/users/login');
        }

        $user_id = session('user_id');

        // Ambil data wishlist
        $query = "SELECT w.*, p.nama_produk, p.harga, p.gambar_produk, p.stok, 
                         bd.harga_diskon, bd.mulai_diskon, bd.selesai_diskon
                  FROM wishlist w
                  JOIN produk p ON w.produk_id = p.produk_id
                  LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                      AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                  WHERE w.user_id = ?
                  ORDER BY w.tanggal_ditambahkan DESC";
        
        $wishlist_items = DB::select($query, [$user_id]);
        
        return view('wishlist', compact('wishlist_items'));
    }

    public function addToCart(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        }

        $user_id = session('user_id');
        $produk_id = $request->input('produk_id');
        $size = $request->input('size', 'M');
        $quantity = 1;

        // Cek apakah produk sudah ada di keranjang
        $existing = DB::select("SELECT * FROM keranjang WHERE user_id = ? AND produk_id = ? AND size = ?", 
                               [$user_id, $produk_id, $size]);

        if ($existing) {
            // Update quantity
            DB::update("UPDATE keranjang SET jumlah = jumlah + 1 WHERE user_id = ? AND produk_id = ? AND size = ?", 
                      [$user_id, $produk_id, $size]);
        } else {
            // Insert new
            DB::insert("INSERT INTO keranjang (user_id, produk_id, size, jumlah) VALUES (?, ?, ?, ?)", 
                       [$user_id, $produk_id, $size, $quantity]);
        }

        return response()->json(['success' => true, 'message' => 'Produk ditambahkan ke keranjang']);
    }

    public function updateCart(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        }

        $cart_id = $request->input('cart_id');
        $action = $request->input('action');

        if ($action === 'increase') {
            DB::update("UPDATE keranjang SET jumlah = jumlah + 1 WHERE keranjang_id = ?", [$cart_id]);
        } elseif ($action === 'decrease') {
            DB::update("UPDATE keranjang SET jumlah = GREATEST(jumlah - 1, 1) WHERE keranjang_id = ?", [$cart_id]);
        }

        return response()->json(['success' => true]);
    }

    public function removeFromCart(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        }

        $cart_id = $request->input('cart_id');
        DB::delete("DELETE FROM keranjang WHERE keranjang_id = ?", [$cart_id]);

        return response()->json(['success' => true]);
    }

    public function addToWishlist(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        }

        $user_id = session('user_id');
        $produk_id = $request->input('produk_id');

        // Cek apakah produk sudah ada di wishlist (exact same as clothStudio)
        $existing = DB::select("SELECT * FROM wishlist WHERE user_id = ? AND produk_id = ?", 
                               [$user_id, $produk_id]);

        if ($existing) {
            return response()->json(['success' => false, 'message' => 'Produk sudah ada di wishlist']);
        }

        // Tambahkan ke wishlist
        DB::insert("INSERT INTO wishlist (user_id, produk_id) VALUES (?, ?)", 
                   [$user_id, $produk_id]);

        return response()->json(['success' => true]);
    }

    public function removeFromWishlist(Request $request)
    {
        if (!session()->has('user_id')) {
            return response()->json(['success' => false, 'message' => 'Silakan login terlebih dahulu']);
        }

        $user_id = session('user_id');
        
        // Support both wishlist_id and produk_id (exact same as clothStudio)
        if ($request->has('wishlist_id')) {
            $wishlist_id = $request->input('wishlist_id');
            DB::delete("DELETE FROM wishlist WHERE wishlist_id = ? AND user_id = ?", [$wishlist_id, $user_id]);
        } elseif ($request->has('produk_id')) {
            $produk_id = $request->input('produk_id');
            DB::delete("DELETE FROM wishlist WHERE produk_id = ? AND user_id = ?", [$produk_id, $user_id]);
        }

        return response()->json(['success' => true]);
    }
}
