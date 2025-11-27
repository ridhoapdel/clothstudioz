<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function showLoginForm()
    {
        return view('users.login');
    }
    
    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ]);
        
        $username = $request->input('username');
        $password = $request->input('password');
        
        $user = DB::select("SELECT * FROM user WHERE username = ? AND role = 'user'", [$username]);
        
        if ($user && Hash::check($password, $user[0]->password)) {
            Session::put('user_id', $user[0]->id);
            Session::put('user_data', (array)$user[0]);
            
            return redirect()->intended('/')->with('success', 'Login berhasil!');
        } else {
            return back()->with('error', 'Username atau password salah!')->withInput();
        }
    }
    
    public function showRegisterForm()
    {
        return view('users.register');
    }
    
    public function register(Request $request)
    {
        $request->validate([
            'username' => 'required|string|unique:user,username',
            'email' => 'required|email|unique:user,email',
            'password' => 'required|string|min:6|confirmed',
        ]);
        
        $username = $request->input('username');
        $email = $request->input('email');
        $password = Hash::make($request->input('password'));
        
        DB::insert("INSERT INTO user (username, email, password, role, created_at, updated_at) 
                   VALUES (?, ?, ?, 'user', NOW(), NOW())", 
                   [$username, $email, $password]);
        
        return redirect()->to('/users/login')->with('success', 'Registrasi berhasil! Silakan login.');
    }
    
    public function logout()
    {
        Session::forget(['user_id', 'user_data']);
        return redirect()->to('/')->with('success', 'Logout berhasil!');
    }
    
    public function profil()
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/users/login');
        }

        $user_id = session('user_id');
        $user = DB::select("SELECT * FROM user WHERE id = ?", [$user_id]);
        
        if (!$user) {
            return redirect()->to('/users/login');
        }
        
        $user = $user[0];
        
        // Get user orders - check if transaksi table exists
        $orders = [];
        try {
            $tables = DB::select("SHOW TABLES LIKE 'transaksi'");
            if (!empty($tables)) {
                $orders = DB::select("SELECT * FROM transaksi WHERE user_id = ? ORDER BY tanggal_transaksi DESC", [$user_id]);
            }
        } catch (\Exception $e) {
            $orders = [];
        }
        
        return view('users.profil', compact('user', 'orders'));
    }
    
    public function updateProfil(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/users/login');
        }

        $user_id = session('user_id');
        $username = $request->input('username');
        $email = $request->input('email');
        
        DB::update("UPDATE user SET username = ?, email = ? WHERE id = ?", [$username, $email, $user_id]);
        
        return back()->with('success', 'Profil berhasil diperbarui');
    }
    
    public function uploadFoto(Request $request)
    {
        if (!session()->has('user_id')) {
            return redirect()->to('/users/login');
        }

        $user_id = session('user_id');
        
        if ($request->hasFile('profile_picture')) {
            $file = $request->file('profile_picture');
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $max_file_size = 2 * 1024 * 1024; // 2MB
            
            if (!in_array($file->getMimeType(), $allowed_types)) {
                return back()->with('error', 'Hanya file JPEG, PNG, atau GIF yang diizinkan.');
            }
            
            if ($file->getSize() > $max_file_size) {
                return back()->with('error', 'Ukuran file maksimum adalah 2MB.');
            }
            
            $file_ext = strtolower($file->getClientOriginalExtension());
            $new_file_name = 'avatar_' . $user_id . '_' . time() . '.' . $file_ext;
            
            $file->move(public_path('uploads/avatars'), $new_file_name);
            
            DB::update("UPDATE user SET profile_picture = ? WHERE id = ?", [$new_file_name, $user_id]);
            
            return back()->with('success', 'Foto profil berhasil diunggah!');
        }
        
        return back()->with('error', 'Tidak ada file yang diunggah.');
    }
}
