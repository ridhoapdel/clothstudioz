<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Session;

class AdminController extends Controller
{
    public function login(Request $request)
    {
        if ($request->isMethod('post')) {
            $username = $request->input('username');
            $password = $request->input('password');
            
            $user = DB::select("SELECT * FROM user WHERE username = ? AND role = 'admin'", [$username]);
            
            if ($user && Hash::check($password, $user[0]->password)) {
                Session::put('admin', (array)$user[0]);
                return redirect()->to('/admin/dashboard');
            } else {
                return back()->with('error', 'Username atau password salah!');
            }
        }
        
        return view('admin.loginAdmin');
    }
    
    public function logout()
    {
        Session::forget('admin');
        return redirect()->to('/admin/login');
    }
    
    public function dashboard()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        // Get dashboard statistics
        $totalProduk = DB::select("SELECT COUNT(*) as count FROM produk")[0]->count;
        $totalUser = DB::select("SELECT COUNT(*) as count FROM user WHERE role = 'user'")[0]->count;
        
        // Check if transaksi table exists before querying
        $totalTransaksi = 0;
        try {
            $tables = DB::select("SHOW TABLES LIKE 'transaksi'");
            if (!empty($tables)) {
                $totalTransaksi = DB::select("SELECT COUNT(*) as count FROM transaksi")[0]->count;
            }
        } catch (\Exception $e) {
            $totalTransaksi = 0;
        }
        
        return view('admin.dashboard', compact('totalProduk', 'totalUser', 'totalTransaksi'));
    }
    
    public function produk()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $products = DB::select("SELECT p.*, bd.diskon_persen, bd.harga_diskon, bd.mulai_diskon, bd.selesai_diskon
                                FROM produk p
                                LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
                                    AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon
                                ORDER BY p.produk_id DESC");
        
        return view('admin.produk', compact('products'));
    }
    
    public function user()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $users = DB::select("SELECT * FROM user WHERE role = 'user' ORDER BY id DESC");
        
        return view('admin.user', compact('users'));
    }
    
    public function transaksi()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        // Check if transaksi table exists
        try {
            $tables = DB::select("SHOW TABLES LIKE 'transaksi'");
            if (empty($tables)) {
                // Return empty array if table doesn't exist
                $transaksi = [];
                return view('admin.transaksi', compact('transaksi'));
            }
            
            $transaksi = DB::select("SELECT t.*, u.username 
                                     FROM transaksi t 
                                     JOIN user u ON t.user_id = u.id 
                                     ORDER BY t.tanggal_transaksi DESC");
        } catch (\Exception $e) {
            $transaksi = [];
        }
        
        return view('admin.transaksi', compact('transaksi'));
    }
    
    public function profil()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $admin = Session::get('admin');
        return view('admin.profiladmin', compact('admin'));
    }
    
    public function updateProfil(Request $request)
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $admin_id = Session::get('admin')['id'];
        $username = $request->input('username');
        $email = $request->input('email');
        
        DB::update("UPDATE user SET username = ?, email = ? WHERE id = ?", [$username, $email, $admin_id]);
        
        // Update session
        $admin = DB::select("SELECT * FROM user WHERE id = ?", [$admin_id])[0];
        Session::put('admin', (array)$admin);
        
        return back()->with('success', 'Profil berhasil diperbarui');
    }
    
    // CRUD Products
    public function createProduk()
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        return view('admin.createProduk');
    }
    
    public function storeProduk(Request $request)
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        // Debug: Log all request data
        \Log::info('Request data: ', $request->all());
        
        // Temporarily remove validation for testing
        /*
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'gambar_produk' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);
        */
        
        $nama_produk = $request->input('nama_produk');
        $deskripsi = $request->input('deskripsi');
        $harga = $request->input('harga');
        
        // Stok per ukuran
        $stok_s = $request->input('stok_s', 0);
        $stok_m = $request->input('stok_m', 0);
        $stok_l = $request->input('stok_l', 0);
        $stok_xl = $request->input('stok_xl', 0);
        
        // Total stok (sum of all sizes)
        $stok = $stok_s + $stok_m + $stok_l + $stok_xl;
        
        $gambar_produk = 'default.jpg';
        
        // Basic validation
        if (empty($nama_produk) || empty($deskripsi) || empty($harga)) {
            return back()->with('error', 'Semua field wajib diisi!')->withInput();
        }
        
        // Debug: Log extracted data
        \Log::info("Extracted data: nama=$nama_produk, deskripsi=$deskripsi, harga=$harga, stok_s=$stok_s, stok_m=$stok_m, stok_l=$stok_l, stok_xl=$stok_xl");
        
        // Handle file upload
        if ($request->hasFile('gambar_produk')) {
            $file = $request->file('gambar_produk');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $gambar_produk = $filename;
            \Log::info("File uploaded: $filename");
        }
        
        try {
            DB::insert("INSERT INTO produk (nama_produk, deskripsi, harga, stok, stok_s, stok_m, stok_l, stok_xl, gambar_produk, created_at, updated_at) 
                       VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())", 
                       [$nama_produk, $deskripsi, $harga, $stok, $stok_s, $stok_m, $stok_l, $stok_xl, $gambar_produk]);
            
            \Log::info("Insert successful for product: $nama_produk");
            return redirect()->to('/admin/produk')->with('success', 'Produk berhasil ditambahkan');
        } catch (\Exception $e) {
            // Debug: log error
            \Log::error("Insert failed: " . $e->getMessage());
            return back()->with('error', 'Gagal menyimpan produk: ' . $e->getMessage())->withInput();
        }
    }
    
    public function editProduk($id)
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $product = DB::select("SELECT * FROM produk WHERE produk_id = ?", [$id]);
        
        if (empty($product)) {
            return redirect()->to('/admin/produk')->with('error', 'Produk tidak ditemukan');
        }
        
        return view('admin.editProduk', ['product' => $product[0]]);
    }
    
    public function updateProduk(Request $request, $id)
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        $request->validate([
            'nama_produk' => 'required|string|max:255',
            'deskripsi' => 'required|string',
            'harga' => 'required|numeric|min:0',
            'stok' => 'required|integer|min:0',
            'gambar_produk' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048',
        ]);
        
        $nama_produk = $request->input('nama_produk');
        $deskripsi = $request->input('deskripsi');
        $harga = $request->input('harga');
        $stok = $request->input('stok');
        
        // Get current product data
        $currentProduct = DB::select("SELECT gambar_produk FROM produk WHERE produk_id = ?", [$id]);
        $gambar_produk = $currentProduct[0]->gambar_produk ?? 'default.jpg';
        
        // Handle file upload
        if ($request->hasFile('gambar_produk')) {
            $file = $request->file('gambar_produk');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path('uploads'), $filename);
            $gambar_produk = $filename;
        }
        
        DB::update("UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, gambar_produk = ?, updated_at = NOW() 
                   WHERE produk_id = ?", 
                   [$nama_produk, $deskripsi, $harga, $stok, $gambar_produk, $id]);
        
        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil diperbarui');
    }
    
    public function deleteProduk($id)
    {
        if (!Session::has('admin')) {
            return redirect()->to('/admin/login');
        }
        
        // Delete from barang_diskon first (foreign key)
        DB::delete("DELETE FROM barang_diskon WHERE produk_id = ?", [$id]);
        
        // Delete from produk
        DB::delete("DELETE FROM produk WHERE produk_id = ?", [$id]);
        
        return redirect()->to('/admin/produk')->with('success', 'Produk berhasil dihapus');
    }
}
