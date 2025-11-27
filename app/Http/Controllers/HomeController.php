<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HomeController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        
        $query = "SELECT p.*, 
                         bd.diskon_persen, 
                         bd.harga_diskon,
                         bd.mulai_diskon,
                         bd.selesai_diskon
                  FROM produk p
                  LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                      AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                  WHERE (p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%')
                  AND p.stok > 0 
                  ORDER BY p.produk_id DESC LIMIT 12";
        
        $products = DB::select($query);
        
        return view('index', compact('products', 'search'));
    }
    
    public function shopAll(Request $request)
    {
        $search = $request->get('search', '');
        
        $query = "SELECT p.*, 
                         bd.diskon_persen, 
                         bd.harga_diskon,
                         bd.mulai_diskon,
                         bd.selesai_diskon
                  FROM produk p
                  LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                      AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                  WHERE (p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%')
                  AND p.stok > 0 
                  ORDER BY p.produk_id DESC";
        
        $products = DB::select($query);
        
        return view('shopAll', compact('products', 'search'));
    }
    
    public function product($id)
    {
        $query = "SELECT p.*, 
                         bd.diskon_persen, 
                         bd.harga_diskon,
                         bd.mulai_diskon,
                         bd.selesai_diskon
                  FROM produk p
                  LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                      AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                  WHERE p.produk_id = ?";
        
        $product = DB::select($query, [$id]);
        
        if (!$product) {
            abort(404);
        }
        
        $product = $product[0];
        
        // Check if product is in user's wishlist (exact same as clothStudio)
        $in_wishlist = false;
        if (session()->has('user_id')) {
            $check_wishlist = DB::select("SELECT * FROM wishlist WHERE user_id = ? AND produk_id = ?", 
                                       [session('user_id'), $id]);
            $in_wishlist = !empty($check_wishlist);
        }
        
        // Get related products
        $relatedQuery = "SELECT p.*, 
                                bd.diskon_persen, 
                                bd.harga_diskon,
                                bd.mulai_diskon,
                                bd.selesai_diskon
                         FROM produk p
                         LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                             AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                         WHERE p.produk_id != ? AND p.stok > 0
                         ORDER BY p.produk_id DESC LIMIT 4";
        
        $relatedProducts = DB::select($relatedQuery, [$id]);
        
        return view('product', compact('product', 'relatedProducts', 'in_wishlist'));
    }
    
    public function search(Request $request)
    {
        $query = $request->get('query', '');
        
        $sql = "SELECT p.*, 
                       bd.diskon_persen, 
                       bd.harga_diskon,
                       bd.mulai_diskon,
                       bd.selesai_diskon
                FROM produk p
                LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                    AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                WHERE (p.nama_produk LIKE '%$query%' OR p.deskripsi LIKE '%$query%')
                AND p.stok > 0 
                ORDER BY p.produk_id DESC";
        
        $products = DB::select($sql);
        
        return view('search_results', compact('products', 'query'));
    }
    
    public function liveSearch(Request $request)
    {
        $query = $request->get('query', '');
        
        if (strlen($query) <= 2) {
            return [];
        }
        
        $sql = "SELECT produk_id, nama_produk, harga, gambar_produk
                FROM produk 
                WHERE (nama_produk LIKE '%$query%' OR deskripsi LIKE '%$query%')
                AND stok > 0 
                ORDER BY nama_produk ASC 
                LIMIT 5";
        
        $products = DB::select($sql);
        
        return response()->json($products);
    }
}
