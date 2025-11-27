<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk - Cloth Studio</title>
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
            <h1 class="text-3xl font-bold text-gray-800">Tambah Produk</h1>
            <p class="text-gray-600">Tambah produk baru ke toko</p>
        </div>

        <!-- Form -->
        <div class="bg-white rounded-lg shadow p-6">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded">
                    {{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="mb-4 p-4 bg-red-100 border border-red-400 text-red-700 rounded">
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ url('/admin/produk') }}" method="POST" enctype="multipart/form-data">
                @csrf
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="nama_produk">
                            Nama Produk <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="nama_produk" 
                               name="nama_produk" 
                               required
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                               placeholder="Masukkan nama produk">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="harga">
                            Harga <span class="text-red-500">*</span>
                        </label>
                        <input type="number" 
                               id="harga" 
                               name="harga" 
                               required
                               min="0"
                               step="0.01"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                               placeholder="Masukkan harga">
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">
                            Stok Per Ukuran <span class="text-red-500">*</span>
                        </label>
                        <div class="grid grid-cols-4 gap-4">
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Size S</label>
                                <input type="number" 
                                       name="stok_s" 
                                       required
                                       min="0"
                                       value="0"
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                                       placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Size M</label>
                                <input type="number" 
                                       name="stok_m" 
                                       required
                                       min="0"
                                       value="0"
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                                       placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Size L</label>
                                <input type="number" 
                                       name="stok_l" 
                                       required
                                       min="0"
                                       value="0"
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                                       placeholder="0">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-600 mb-1">Size XL</label>
                                <input type="number" 
                                       name="stok_xl" 
                                       required
                                       min="0"
                                       value="0"
                                       class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                                       placeholder="0">
                            </div>
                        </div>
                    </div>

                    <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2" for="gambar_produk">
                            Gambar Produk
                        </label>
                        <input type="file" 
                               id="gambar_produk" 
                               name="gambar_produk" 
                               accept="image/*"
                               class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500">
                        <p class="text-xs text-gray-500 mt-1">Pilih file gambar dari device (JPG, PNG, GIF)</p>
                    </div>
                </div>

                <div class="mt-6">
                    <label class="block text-gray-700 text-sm font-bold mb-2" for="deskripsi">
                        Deskripsi <span class="text-red-500">*</span>
                    </label>
                    <textarea id="deskripsi" 
                              name="deskripsi" 
                              required
                              rows="4"
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring focus:border-blue-500"
                              placeholder="Masukkan deskripsi produk"></textarea>
                </div>

                <div class="mt-8 flex justify-end space-x-4">
                    <a href="{{ url('/admin/produk') }}" 
                       class="px-4 py-2 border border-gray-300 rounded-lg hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" 
                            class="px-4 py-2 bg-black text-white rounded-lg hover:bg-gray-800 transition">
                        <i class="fas fa-save mr-2"></i> Simpan Produk
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
