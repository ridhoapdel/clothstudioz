@extends('layouts.app')

@section('title', 'Profil Saya')

@section('content')
@if(!session()->has('user_id'))
    <div class="text-center py-12">
        <i class="fa fa-user text-5xl text-gray-300 mb-4"></i>
        <p class="text-xl text-gray-600 mb-4">Silakan login terlebih dahulu</p>
        <a href="{{ url('/users/login') }}" class="bg-black text-white px-6 py-2 rounded-lg hover:bg-gray-800 transition">
            Login
        </a>
    </div>
@else
    <main class="container mx-auto px-4 mt-20 py-8">
        <div class="flex flex-col md:flex-row gap-8">
            <!-- Sidebar Menu -->
            <div class="w-full md:w-1/4 lg:w-1/5 bg-white rounded-lg shadow-md p-4">
                <div class="flex flex-col items-center py-4 border-b">
                    <div class="w-20 h-20 rounded-full bg-gray-300 mb-3 overflow-hidden relative avatar-upload">
                        <img src="{{ isset($user->profile_picture) && $user->profile_picture ? asset('uploads/avatars/' . $user->profile_picture) : asset('aset/user-avatar.png') }}" alt="User Avatar" class="w-full h-full object-cover">
                        <form id="avatarForm" enctype="multipart/form-data" method="POST" action="{{ url('/user/upload-foto') }}">
                            @csrf
                            <label for="profile_picture" class="avatar-upload-label">
                                <i class="fa fa-camera"></i>
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png,image/gif" onchange="document.getElementById('avatarForm').submit()">
                        </form>
                    </div>
                    @if(session('success'))
                        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded mt-2 text-sm text-center" role="alert">
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mt-2 text-sm text-center" role="alert">
                            {{ session('error') }}
                        </div>
                    @endif
                    <h3 class="font-semibold text-lg">{{ $user->username }}</h3>
                    <p class="text-gray-500 text-sm">{{ $user->email ?? 'Email belum diatur' }}</p>
                </div>
                <nav class="mt-4">
                    <ul class="space-y-2">
                        <li>
                            <a href="#" class="flex items-center px-4 py-2 text-gray-700 bg-gray-100 rounded-md">
                                <i class="fa fa-user mr-3"></i>
                                Profil Saya
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/keranjang') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                                <i class="fa fa-shopping-cart mr-3"></i>
                                Keranjang
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/wishlist') }}" class="flex items-center px-4 py-2 text-gray-700 hover:bg-gray-100 rounded-md">
                                <i class="fa fa-heart mr-3"></i>
                                Wishlist
                            </a>
                        </li>
                        <li>
                            <a href="{{ url('/users/logout') }}" class="flex items-center px-4 py-2 text-red-600 hover:bg-red-50 rounded-md">
                                <i class="fa fa-sign-out mr-3"></i>
                                Keluar
                            </a>
                        </li>
                    </ul>
                </nav>
            </div>

            <!-- Main Content -->
            <div class="w-full md:w-3/4 lg:w-4/5">
                <!-- Profile Information -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-xl font-bold mb-4">Informasi Profil</h2>
                    <form action="{{ url('/user/update-profil') }}" method="POST">
                        @csrf
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                                <input type="text" name="username" value="{{ $user->username }}" required
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                                <input type="email" name="email" value="{{ $user->email ?? '' }}"
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                        
                        <div class="mt-6">
                            <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700 transition">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Orders History -->
                <div class="bg-white rounded-lg shadow-md p-6">
                    <h2 class="text-xl font-bold mb-4">Riwayat Pesanan</h2>
                    
                    @if(count($orders) > 0)
                        <div class="space-y-4">
                            @foreach($orders as $order)
                                <div class="border rounded-lg p-4 {{ isset($_GET['transaksi_id']) && $_GET['transaksi_id'] == $order->id ? 'new-order' : '' }}">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <p class="font-semibold">Order #{{ $order->id ?? $order->transaksi_id ?? 'N/A' }}</p>
                                            <p class="text-sm text-gray-600">
                                                {{ $order->tanggal_transaksi ? date('d M Y H:i', strtotime($order->tanggal_transaksi)) : '-' }}
                                            </p>
                                            <p class="text-sm">Total: Rp {{ number_format($order->total ?? 0, 0, ',', '.') }}</p>
                                            <p class="text-sm">Metode: {{ $order->metode_pembayaran ?? '-' }}</p>
                                        </div>
                                        <div class="text-right">
                                            <span class="px-3 py-1 text-xs rounded-full bg-yellow-100 text-yellow-800">
                                                {{ $order->status ?? 'pending' }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-gray-600">Belum ada pesanan.</p>
                    @endif
                </div>
            </div>
        </div>
    </main>
@endif
@endsection

<style>
    .avatar-upload {
        position: relative;
        display: inline-block;
    }
    .avatar-upload input[type="file"] {
        display: none;
    }
    .avatar-upload-label {
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        position: absolute;
        bottom: 0;
        right: 0;
        width: 32px;
        height: 32px;
        background-color: #3b82f6;
        color: white;
        border-radius: 50%;
        transition: background-color 0.3s;
    }
    .avatar-upload-label:hover {
        background-color: #2563eb;
    }
    .new-order {
        background-color: #fefcbf;
        border-left: 4px solid #facc15;
    }
</style>