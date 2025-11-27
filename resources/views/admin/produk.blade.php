<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Produk - Cloth Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>
<body class="bg-gray-50">
    <!-- Sidebar -->
    <div class="fixed inset-y-0 left-0 z-50 w-64 bg-white shadow-lg">
        <div class="flex items-center justify-center h-16 bg-black">
            <img src="{{ asset('aset/png logo.png') }}" alt="Logo" class="h-12">
        </div>
        <nav class="mt-8">
            <a href="{{ url('/admin/dashboard') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-tachometer-alt mr-3"></i> Dashboard
            </a>
            <a href="{{ url('/admin/produk') }}" class="flex items-center px-6 py-3 bg-gray-100 text-gray-900">
                <i class="fas fa-box mr-3"></i> Produk
            </a>
            <a href="{{ url('/admin/user') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-users mr-3"></i> User
            </a>
            <a href="{{ url('/admin/transaksi') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-shopping-cart mr-3"></i> Transaksi
            </a>
            <a href="{{ url('/admin/laporan') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-chart-bar mr-3"></i> Laporan
            </a>
            <a href="{{ url('/admin/profil') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
                <i class="fas fa-user mr-3"></i> Profil
            </a>
            <a href="{{ url('/admin/logout') }}" class="flex items-center px-6 py-3 text-red-600 hover:bg-red-50">
                <i class="fas fa-sign-out-alt mr-3"></i> Logout
            </a>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="mb-8 flex justify-between items-center">
            <div>
                <h1 class="text-3xl font-bold text-gray-800">Manajemen Produk</h1>
                <p class="text-gray-600">Kelola semua produk di toko</p>
            </div>
            <a href="{{ url('/admin/produk/create') }}" class="bg-black text-white px-4 py-2 rounded hover:bg-gray-800 transition">
                <i class="fas fa-plus mr-2"></i> Tambah Produk
            </a>
        </div>

        <!-- Search Bar -->
        <div class="bg-white rounded-lg shadow p-4 mb-6">
            <form method="GET" class="flex items-center">
                <input type="text" name="search" placeholder="Cari produk..." 
                       value="{{ request()->get('search') }}" 
                       class="flex-1 px-4 py-2 border rounded-l focus:outline-none focus:ring">
                <button type="submit" class="bg-black text-white px-4 py-2 rounded-r hover:bg-gray-800 transition">
                    <i class="fas fa-search"></i>
                </button>
            </form>
        </div>

        <!-- Products Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4">ID</th>
                            <th class="text-left py-3 px-4">Gambar</th>
                            <th class="text-left py-3 px-4">Nama Produk</th>
                            <th class="text-left py-3 px-4">Harga</th>
                            <th class="text-left py-3 px-4">Stok</th>
                            <th class="text-left py-3 px-4">Status</th>
                            <th class="text-left py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($products as $product)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $product->produk_id }}</td>
                                <td class="py-3 px-4">
                                    <img src="{{ asset('uploads/' . $product->gambar_produk) }}" 
                                         alt="{{ $product->nama_produk }}" 
                                         class="w-16 h-16 object-cover rounded">
                                </td>
                                <td class="py-3 px-4">{{ $product->nama_produk }}</td>
                                <td class="py-3 px-4">
                                    @if(!empty($product->harga_diskon))
                                        <span class="text-gray-500 line-through text-sm">
                                            Rp {{ number_format($product->harga, 0, ',', '.') }}
                                        </span>
                                        <br>
                                        <span class="font-bold">
                                            Rp {{ number_format($product->harga_diskon, 0, ',', '.') }}
                                        </span>
                                    @else
                                        Rp {{ number_format($product->harga, 0, ',', '.') }}
                                    @endif
                                </td>
                                <td class="py-3 px-4">{{ $product->stok }}</td>
                                <td class="py-3 px-4">
                                    @if($product->stok > 0)
                                        <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Tersedia</span>
                                    @else
                                        <span class="px-2 py-1 text-xs rounded-full bg-red-100 text-red-800">Habis</span>
                                    @endif
                                </td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ url('/admin/produk/' . $product->produk_id . '/edit') }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form action="{{ url('/admin/produk/' . $product->produk_id) }}" method="POST" style="display: inline;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="text-red-600 hover:text-red-800"
                                                    onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
