<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin - Cloth Studio</title>
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
            <a href="{{ url('/admin/profil') }}" class="flex items-center px-6 py-3 bg-gray-100 text-gray-900">
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
            <h1 class="text-3xl font-bold text-gray-800">Profil Admin</h1>
            <p class="text-gray-600">Kelola informasi profil admin</p>
        </div>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow p-6">
            <form action="{{ url('/admin/profil') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Username</label>
                        <input type="text" name="username" value="{{ $admin['username'] }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                        <input type="email" name="email" value="{{ $admin['email'] ?? '' }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Role</label>
                        <input type="text" value="{{ $admin['role'] }}" readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal Bergabung</label>
                        <input type="text" value="{{ isset($admin['created_at']) ? date('d M Y', strtotime($admin['created_at'])) : '-' }}" readonly
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-gray-100">
                    </div>
                </div>

                <div class="flex justify-end space-x-4">
                    <a href="{{ url('/admin/dashboard') }}" class="px-6 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
