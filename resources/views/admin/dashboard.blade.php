<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Cloth Studio</title>
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
            <a href="{{ url('/admin/produk') }}" class="flex items-center px-6 py-3 text-gray-700 hover:bg-gray-100">
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
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-gray-800">Dashboard Admin</h1>
            <p class="text-gray-600">Selamat datang di panel admin Cloth Studio</p>
        </div>

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-blue-500 rounded-full">
                        <i class="fas fa-box text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Produk</p>
                        <p class="text-2xl font-bold">{{ $totalProduk }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-green-500 rounded-full">
                        <i class="fas fa-users text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total User</p>
                        <p class="text-2xl font-bold">{{ $totalUser }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-yellow-500 rounded-full">
                        <i class="fas fa-shopping-cart text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Total Transaksi</p>
                        <p class="text-2xl font-bold">{{ $totalTransaksi }}</p>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-lg shadow">
                <div class="flex items-center">
                    <div class="p-3 bg-purple-500 rounded-full">
                        <i class="fas fa-chart-line text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-gray-500 text-sm">Pendapatan</p>
                        <p class="text-2xl font-bold">Rp 0</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Products -->
        <div class="bg-white rounded-lg shadow p-6">
            <h2 class="text-xl font-bold mb-4">Produk Terbaru</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="border-b">
                            <th class="text-left py-3 px-4">ID</th>
                            <th class="text-left py-3 px-4">Nama Produk</th>
                            <th class="text-left py-3 px-4">Harga</th>
                            <th class="text-left py-3 px-4">Stok</th>
                            <th class="text-left py-3 px-4">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="border-b hover:bg-gray-50">
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4">-</td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">Aktif</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
