<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen User - Cloth Studio</title>
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
            <a href="{{ url('/admin/user') }}" class="flex items-center px-6 py-3 bg-gray-100 text-gray-900">
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
            <h1 class="text-3xl font-bold text-gray-800">Manajemen User</h1>
            <p class="text-gray-600">Kelola semua user yang terdaftar</p>
        </div>

        <!-- Users Table -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="text-left py-3 px-4">ID</th>
                            <th class="text-left py-3 px-4">Username</th>
                            <th class="text-left py-3 px-4">Email</th>
                            <th class="text-left py-3 px-4">Role</th>
                            <th class="text-left py-3 px-4">Tanggal Daftar</th>
                            <th class="text-left py-3 px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                            <tr class="border-b hover:bg-gray-50">
                                <td class="py-3 px-4">{{ $user->id }}</td>
                                <td class="py-3 px-4">{{ $user->username }}</td>
                                <td class="py-3 px-4">{{ $user->email ?? '-' }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="py-3 px-4">{{ $user->created_at->format('d M Y') }}</td>
                                <td class="py-3 px-4">
                                    <div class="flex space-x-2">
                                        <a href="{{ url('/admin/user-detail/' . $user->id) }}" 
                                           class="text-blue-600 hover:text-blue-800">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <button onclick="confirmDelete({{ $user->id }})" 
                                                class="text-red-600 hover:text-red-800">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
    function confirmDelete(userId) {
        if (confirm('Apakah Anda yakin ingin menghapus user ini?')) {
            window.location.href = '{{ url("/admin/hapus-user") }}?id=' + userId;
        }
    }
    </script>
</body>
</html>
