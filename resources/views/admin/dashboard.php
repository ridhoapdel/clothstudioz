<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Get selected user if any
$selected_user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;

// Handle search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query for products
$query_produk = "SELECT p.*, 
                 bd.diskon_persen, 
                 bd.harga_diskon,
                 bd.mulai_diskon,
                 bd.selesai_diskon
          FROM produk p
          LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
              AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon";

if (!empty($search)) {
    $query_produk .= " WHERE p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%'";
}

$query_produk .= " ORDER BY p.produk_id DESC";
$result_produk = mysqli_query($conn, $query_produk);

// Statistics queries (1 month only)
$total_produk = mysqli_query($conn, "SELECT COUNT(*) FROM produk")->fetch_row()[0];
$total_user = mysqli_query($conn, "SELECT COUNT(*) FROM user WHERE role='pelanggan'")->fetch_row()[0];
$total_transaksi = mysqli_query($conn, "SELECT COUNT(*) FROM transaksi WHERE tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetch_row()[0];
$total_pendapatan = mysqli_query($conn, "SELECT SUM(total) FROM transaksi WHERE status='completed' AND tanggal_transaksi >= DATE_SUB(CURDATE(), INTERVAL 1 MONTH)")->fetch_row()[0] ?? 0;

// Sales data for chart (last 30 days)
$sales_data = [];
$sales_labels = [];
for ($i = 29; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $query = "SELECT SUM(total) FROM transaksi 
              WHERE status='completed' 
              AND DATE(tanggal_transaksi) = '$date'";
    $amount = mysqli_query($conn, $query)->fetch_row()[0] ?? 0;
    $sales_data[] = $amount;
    $sales_labels[] = date('d M', strtotime($date));
}

// User list
$users = mysqli_query($conn, "SELECT * FROM user WHERE role='pelanggan' ORDER BY username");

// Get detailed user data if selected
$user_details = null;
$user_wishlist = [];
$user_keranjang = [];
$user_transaksi = [];
$user_ulasan = [];
$user_help = [];

if ($selected_user_id > 0) {
    // User details
    $user_details = mysqli_query($conn, "SELECT * FROM user WHERE user_id = $selected_user_id")->fetch_assoc();
    
    // User wishlist
    $user_wishlist = mysqli_query($conn, "
        SELECT w.*, p.nama_produk, p.harga, p.gambar_produk 
        FROM wishlist w
        JOIN produk p ON w.produk_id = p.produk_id
        WHERE w.user_id = $selected_user_id
        ORDER BY w.tanggal_ditambahkan DESC
    ");
    
    // User cart
    $user_keranjang = mysqli_query($conn, "
        SELECT k.*, p.nama_produk, p.harga, p.gambar_produk, pv.ukuran
        FROM keranjang k
        JOIN produk p ON k.produk_id = p.produk_id
        LEFT JOIN produk_variasi pv ON (k.size = pv.ukuran AND k.produk_id = pv.produk_id)
        WHERE k.user_id = $selected_user_id
        ORDER BY k.tanggal_ditambahkan DESC
    ");
    
    // User transactions
    $user_transaksi = mysqli_query($conn, "
        SELECT t.*, k.nama_kurir, k.biaya_pengiriman, k.estimasi_pengiriman,
               (SELECT COUNT(*) FROM item_transaksi it WHERE it.transaksi_id = t.transaksi_id) as jumlah_item
        FROM transaksi t
        LEFT JOIN kurir k ON t.kurir_id = k.kurir_id
        WHERE t.user_id = $selected_user_id
        ORDER BY t.tanggal_transaksi DESC
    ");
    
    // User reviews
    $user_ulasan = mysqli_query($conn, "
        SELECT u.*, p.nama_produk, p.gambar_produk
        FROM ulasan u
        JOIN produk p ON u.produk_id = p.produk_id
        WHERE u.user_id = $selected_user_id
        ORDER BY u.tanggal_ulasan DESC
    ");
    
    // User help requests
    $user_help = mysqli_query($conn, "
        SELECT * FROM help_center
        WHERE user_id = $selected_user_id
        ORDER BY created_at DESC
    ");
}

// Recent activities
$aktivitas = mysqli_query($conn, "
    (SELECT 'wishlist' as jenis, w.tanggal_ditambahkan as waktu, u.username, p.nama_produk, NULL as nilai
     FROM wishlist w 
     JOIN user u ON w.user_id = u.user_id 
     JOIN produk p ON w.produk_id = p.produk_id
     ORDER BY w.tanggal_ditambahkan DESC LIMIT 5)
    
    UNION
    
    (SELECT 'keranjang' as jenis, k.tanggal_ditambahkan as waktu, u.username, p.nama_produk, NULL as nilai
     FROM keranjang k 
     JOIN user u ON k.user_id = u.user_id 
     JOIN produk p ON k.produk_id = p.produk_id
     ORDER BY k.tanggal_ditambahkan DESC LIMIT 5)
    
    UNION
    
    (SELECT 'transaksi' as jenis, t.tanggal_transaksi as waktu, u.username, NULL as nama_produk, t.total as nilai
     FROM transaksi t 
     JOIN user u ON t.user_id = u.user_id
     ORDER BY t.tanggal_transaksi DESC LIMIT 5)
    
    UNION
    
    (SELECT 'ulasan' as jenis, ul.tanggal_ulasan as waktu, u.username, p.nama_produk, ul.rating as nilai
     FROM ulasan ul 
     JOIN user u ON ul.user_id = u.user_id 
     JOIN produk p ON ul.produk_id = p.produk_id
     ORDER BY ul.tanggal_ulasan DESC LIMIT 5)
    
    UNION
    
    (SELECT 'help' as jenis, h.created_at as waktu, u.username, h.kategori as nama_produk, NULL as nilai
     FROM help_center h 
     JOIN user u ON h.user_id = u.user_id
     ORDER BY h.created_at DESC LIMIT 5)
    
    ORDER BY waktu DESC LIMIT 10
");

// Recent articles
$artikel = mysqli_query($conn, "SELECT * FROM artikel ORDER BY tanggal_publish DESC LIMIT 5");

// Pagination settings
$per_page = 10;
$total_results = mysqli_num_rows($result_produk);
$total_pages = ceil($total_results / $per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $per_page;

// Modify query with pagination
$query_produk .= " LIMIT $offset, $per_page";
$result_produk = mysqli_query($conn, $query_produk);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Laviade</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <div class="flex h-screen">
        <!-- Sidebar/Navbar Combined -->
        <div class="w-64 bg-gray-800 text-white flex flex-col">
            <!-- Logo/Brand -->
            <div class="p-4 border-b border-gray-700">
                <h1 class="text-xl font-bold">Laviade Admin</h1>
            </div>
            
            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto">
                <div class="p-4">
                    <h3 class="text-xs uppercase text-gray-400 font-bold mb-2">Main</h3>
                    <ul>
                        <li>
                            <a href="dashboard.php" class="block py-2 px-3 rounded bg-gray-700 text-white">
                                <i class="fas fa-tachometer-alt mr-2"></i> Dashboard
                            </a>
                        </li>
                        <li>
                            <a href="produk.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-boxes mr-2"></i> Produk
                            </a>
                        </li>
                        <li>
                            <a href="transaksi.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-shopping-cart mr-2"></i> Transaksi
                            </a>
                        </li>
                        <li>
                            <a href="user.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-users mr-2"></i> User
                            </a>
                        </li>
                        <li>
                            <a href="artikel.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-newspaper mr-2"></i> Artikel
                            </a>
                        </li>
                    </ul>
                </div>
                
                <div class="p-4 border-t border-gray-700">
                    <h3 class="text-xs uppercase text-gray-400 font-bold mb-2">Reports</h3>
                    <ul>
                        <li>
                            <a href="laporan_penjualan.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-chart-line mr-2"></i> Laporan Penjualan
                            </a>
                        </li>
                        <li>
                            <a href="laporan_produk.php" class="block py-2 px-3 rounded hover:bg-gray-700 text-gray-300 hover:text-white">
                                <i class="fas fa-chart-pie mr-2"></i> Laporan Produk
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
            
            <!-- User Profile -->
            <div class="p-4 border-t border-gray-700">
                <div class="flex items-center">
                    <div class="w-10 h-10 rounded-full bg-gray-600 flex items-center justify-center">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="ml-3">
                        <p class="font-medium"><?= htmlspecialchars($_SESSION['admin']['username'] ?? 'Admin') ?></p>
                        <p class="text-xs text-gray-400">Administrator</p>
                    </div>
                </div>
                <a href="logout.php" class="block mt-4 text-center py-2 px-4 bg-red-600 hover:bg-red-700 rounded text-sm">
                    <i class="fas fa-sign-out-alt mr-1"></i> Logout
                </a>
            </div>
        </div>

        <!-- Main Content -->
        <div class="flex-1 overflow-auto">
            <!-- Topbar -->
            <header class="bg-white shadow-sm">
                <div class="flex justify-between items-center px-6 py-4">
                    <h2 class="text-xl font-semibold text-gray-800">Dashboard</h2>
                    <div class="flex items-center">
                        <div class="relative">
                            <input type="text" class="border border-gray-300 rounded-lg pl-10 pr-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" placeholder="Search...">
                            <div class="absolute left-3 top-2.5 text-gray-400">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <button class="ml-4 p-2 rounded-full bg-gray-100 text-gray-600 hover:bg-gray-200">
                            <i class="fas fa-bell"></i>
                        </button>
                    </div>
                </div>
            </header>

            <div class="p-6">
                <?php if(isset($_GET['success'])): ?>
                    <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
                        <p class="font-bold">Success!</p>
                        <p>Produk telah berhasil <?= $_GET['success'] == 'edit' ? 'diperbarui' : 'ditambahkan' ?>.</p>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-blue-100 text-blue-600 mr-4">
                                <i class="fas fa-box text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Produk</p>
                                <h3 class="text-2xl font-bold"><?= number_format($total_produk) ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-green-100 text-green-600 mr-4">
                                <i class="fas fa-users text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total User</p>
                                <h3 class="text-2xl font-bold"><?= number_format($total_user) ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-purple-100 text-purple-600 mr-4">
                                <i class="fas fa-shopping-cart text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Transaksi</p>
                                <h3 class="text-2xl font-bold"><?= number_format($total_transaksi) ?></h3>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex items-center">
                            <div class="p-3 rounded-full bg-yellow-100 text-yellow-600 mr-4">
                                <i class="fas fa-money-bill-wave text-xl"></i>
                            </div>
                            <div>
                                <p class="text-gray-500">Total Pendapatan</p>
                                <h3 class="text-2xl font-bold">Rp <?= number_format($total_pendapatan, 0, ',', '.') ?></h3>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Two Columns (Chart and User List) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Sales Chart -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b">
                            <h3 class="font-bold text-lg">Penjualan 30 Hari Terakhir</h3>
                        </div>
                        <div class="p-4">
                            <canvas id="salesChart" height="250"></canvas>
                        </div>
                    </div>

                    <!-- User List -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b">
                            <h3 class="font-bold text-lg">Daftar User</h3>
                        </div>
                        <div class="divide-y max-h-96 overflow-y-auto">
                            <?php while($user = mysqli_fetch_assoc($users)): ?>
                            <a href="?user_id=<?= $user['user_id'] ?>" class="block p-4 hover:bg-gray-50 <?= $selected_user_id == $user['user_id'] ? 'bg-blue-50' : '' ?>">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                        <?php if(!empty($user['profile_picture'])): ?>
                                            <img src="../Uploads/<?= htmlspecialchars($user['profile_picture']) ?>" class="h-full w-full object-cover">
                                        <?php else: ?>
                                            <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                <i class="fas fa-user"></i>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="ml-4">
                                        <h4 class="text-sm font-medium text-gray-900"><?= htmlspecialchars($user['username']) ?></h4>
                                        <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email']) ?></p>
                                    </div>
                                    <div class="ml-auto">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                            Pelanggan
                                        </span>
                                    </div>
                                </div>
                            </a>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- User Details Section (if user selected) -->
                <?php if($selected_user_id > 0 && $user_details): ?>
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="font-bold text-lg">Detail User: <?= htmlspecialchars($user_details['username']) ?></h3>
                        <a href="?user_id=" class="text-sm text-blue-500 hover:text-blue-700">
                            <i class="fas fa-times mr-1"></i> Tutup
                        </a>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-0">
                        <!-- User Profile -->
                        <div class="p-6 border-r border-b">
                            <div class="flex flex-col items-center">
                                <div class="h-24 w-24 rounded-full overflow-hidden mb-4">
                                    <?php if(!empty($user_details['profile_picture'])): ?>
                                        <img src="../Uploads/<?= htmlspecialchars($user_details['profile_picture']) ?>" class="h-full w-full object-cover">
                                    <?php else: ?>
                                        <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500 text-4xl">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <h3 class="text-lg font-medium"><?= htmlspecialchars($user_details['username']) ?></h3>
                                <p class="text-gray-500 mb-2"><?= htmlspecialchars($user_details['email']) ?></p>
                                <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                                    Pelanggan
                                </span>
                                <p class="text-sm text-gray-500 mt-4">
                                    Bergabung sejak: <?= date('d M Y', strtotime($user_details['created_at'])) ?>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Wishlist -->
                        <div class="p-4 border-b md:border-r">
                            <h4 class="font-medium mb-3">
                                <i class="fas fa-heart text-red-500 mr-2"></i> Wishlist
                                <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5 ml-1"><?= mysqli_num_rows($user_wishlist) ?></span>
                            </h4>
                            <div class="space-y-3 max-h-60 overflow-y-auto">
                                <?php if(mysqli_num_rows($user_wishlist) > 0): ?>
                                    <?php while($item = mysqli_fetch_assoc($user_wishlist)): ?>
                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                                        <div class="flex-shrink-0 h-10 w-10 rounded overflow-hidden">
                                            <img src="../Uploads/<?= htmlspecialchars($item['gambar_produk']) ?>" class="h-full w-full object-cover">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                            <p class="text-xs text-gray-500">Rp <?= number_format($item['harga'], 0, ',', '.') ?></p>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500 text-center py-4">Tidak ada produk di wishlist</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Keranjang -->
                        <div class="p-4 border-b">
                            <h4 class="font-medium mb-3">
                                <i class="fas fa-shopping-cart text-blue-500 mr-2"></i> Keranjang
                                <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5 ml-1"><?= mysqli_num_rows($user_keranjang) ?></span>
                            </h4>
                            <div class="space-y-3 max-h-60 overflow-y-auto">
                                <?php if(mysqli_num_rows($user_keranjang) > 0): ?>
                                    <?php while($item = mysqli_fetch_assoc($user_keranjang)): ?>
                                    <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                                        <div class="flex-shrink-0 h-10 w-10 rounded overflow-hidden">
                                            <img src="../Uploads/<?= htmlspecialchars($item['gambar_produk']) ?>" class="h-full w-full object-cover">
                                        </div>
                                        <div class="ml-3">
                                            <p class="text-sm font-medium"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                            <p class="text-xs text-gray-500">
                                                Rp <?= number_format($item['harga'], 0, ',', '.') ?> 
                                                <?= $item['ukuran'] ? "| Size: " . htmlspecialchars($item['ukuran']) : '' ?>
                                            </p>
                                        </div>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500 text-center py-4">Keranjang kosong</p>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Help Center -->
                        <div class="p-4 border-b md:border-r">
                            <h4 class="font-medium mb-3">
                                <i class="fas fa-question-circle text-purple-500 mr-2"></i> Help Center
                                <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5 ml-1"><?= mysqli_num_rows($user_help) ?></span>
                            </h4>
                            <div class="space-y-3 max-h-60 overflow-y-auto">
                                <?php if(mysqli_num_rows($user_help) > 0): ?>
                                    <?php while($item = mysqli_fetch_assoc($user_help)): ?>
                                    <div class="p-2 hover:bg-gray-50 rounded">
                                        <p class="text-sm font-medium">
                                            <?= htmlspecialchars($item['kategori']) ?> - <?= htmlspecialchars($item['nama']) ?>
                                        </p>
                                        <p class="text-xs text-gray-500 truncate"><?= htmlspecialchars($item['pesan']) ?></p>
                                        <p class="text-xs text-gray-400 mt-1">
                                            <?= date('d M Y H:i', strtotime($item['created_at'])) ?>
                                        </p>
                                    </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <p class="text-sm text-gray-500 text-center py-4">Tidak ada permintaan bantuan</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Transactions -->
                    <div class="p-4 border-t">
                        <h4 class="font-medium mb-3">
                            <i class="fas fa-receipt text-green-500 mr-2"></i> Riwayat Transaksi
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Total</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembayaran</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kurir</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php if(mysqli_num_rows($user_transaksi) > 0): ?>
                                        <?php while($trans = mysqli_fetch_assoc($user_transaksi)): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?= htmlspecialchars($trans['kode_transaksi']) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y H:i', strtotime($trans['tanggal_transaksi'])) ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp <?= number_format($trans['total'], 0, ',', '.') ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?= $trans['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                                       ($trans['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                                       'bg-blue-100 text-blue-800') ?>">
                                                    <?= ucfirst($trans['status']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= ucfirst($trans['metode_pembayaran']) ?>
                                                <span class="block text-xs <?= $trans['status_pembayaran'] == 'paid' ? 'text-green-600' : 'text-yellow-600' ?>">
                                                    <?= ucfirst($trans['status_pembayaran']) ?>
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                <?= htmlspecialchars($trans['nama_kurir'] ?? '-') ?>
                                                <?php if($trans['nomor_resi']): ?>
                                                    <span class="block text-xs">Resi: <?= htmlspecialchars($trans['nomor_resi']) ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <a href="transaksi_detail.php?id=<?= $trans['transaksi_id'] ?>" class="text-blue-600 hover:text-blue-900 mr-3">Detail</a>
                                            </td>
                                        </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada transaksi</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    <!-- Reviews -->
                    <div class="p-4 border-t">
                        <h4 class="font-medium mb-3">
                            <i class="fas fa-star text-yellow-500 mr-2"></i> Ulasan Produk
                        </h4>
                        <div class="space-y-4">
                            <?php if(mysqli_num_rows($user_ulasan) > 0): ?>
                                <?php while($review = mysqli_fetch_assoc($user_ulasan)): ?>
                                <div class="border rounded-lg p-4">
                                    <div class="flex items-start">
                                        <div class="flex-shrink-0 h-10 w-10 rounded overflow-hidden">
                                            <img src="../Uploads/<?= htmlspecialchars($review['gambar_produk']) ?>" class="h-full w-full object-cover">
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="flex items-center justify-between">
                                                <h5 class="font-medium"><?= htmlspecialchars($review['nama_produk']) ?></h5>
                                                <div class="flex items-center">
                                                    <?php for($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?= $i <= $review['rating'] ? 'text-yellow-400' : 'text-gray-300' ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($review['komentar']) ?></p>
                                            <p class="text-xs text-gray-400 mt-2">
                                                <?= date('d M Y H:i', strtotime($review['tanggal_ulasan'])) ?>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-sm text-gray-500 text-center py-4">Belum ada ulasan produk</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Two Columns (Activities and Articles) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
                    <!-- Recent Activities -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b">
                            <h3 class="font-bold text-lg">Aktivitas Terbaru</h3>
                        </div>
                        <div class="divide-y">
                            <?php while($activity = mysqli_fetch_assoc($aktivitas)): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex items-center">
                                    <div class="flex-shrink-0">
                                        <i class="fas <?= 
                                            $activity['jenis'] == 'wishlist' ? 'fa-heart text-red-500' :
                                            ($activity['jenis'] == 'keranjang' ? 'fa-shopping-cart text-blue-500' :
                                            ($activity['jenis'] == 'transaksi' ? 'fa-receipt text-green-500' :
                                            ($activity['jenis'] == 'ulasan' ? 'fa-star text-yellow-500' :
                                            'fa-question-circle text-purple-500'))) ?> mr-3"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm">
                                            <span class="font-medium"><?= htmlspecialchars($activity['username']) ?></span> 
                                            <?= 
                                                $activity['jenis'] == 'wishlist' ? 'menambahkan ' . htmlspecialchars($activity['nama_produk']) . ' ke wishlist' :
                                                ($activity['jenis'] == 'keranjang' ? 'menambahkan ' . htmlspecialchars($activity['nama_produk']) . ' ke keranjang' :
                                                ($activity['jenis'] == 'transaksi' ? 'melakukan transaksi Rp ' . number_format($activity['nilai'], 0, ',', '.') :
                                                ($activity['jenis'] == 'ulasan' ? 'memberikan ulasan ' . $activity['nilai'] . ' bintang untuk ' . htmlspecialchars($activity['nama_produk']) :
                                                'mengajukan bantuan untuk ' . htmlspecialchars($activity['nama_produk'])))) 
                                            ?>
                                        </p>
                                        <p class="text-xs text-gray-400">
                                            <?= date('d M Y H:i', strtotime($activity['waktu'])) ?>
                                        </p>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>

                    <!-- Recent Articles -->
                    <div class="bg-white rounded-lg shadow overflow-hidden">
                        <div class="p-4 border-b flex justify-between items-center">
                            <h3 class="font-bold text-lg">Artikel Terbaru</h3>
                            <a href="tambah_artikel.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                                <i class="fas fa-plus mr-1"></i> Tambah Artikel
                            </a>
                        </div>
                        <div class="divide-y">
                            <?php while($art = mysqli_fetch_assoc($artikel)): ?>
                            <div class="p-4 hover:bg-gray-50">
                                <div class="flex">
                                    <?php if($art['gambar']): ?>
                                    <div class="mr-3 flex-shrink-0">
                                        <img src="../Uploads/<?= htmlspecialchars($art['gambar']) ?>" class="w-16 h-16 object-cover rounded">
                                    </div>
                                    <?php endif; ?>
                                    <div class="flex-1">
                                        <h4 class="font-medium"><?= htmlspecialchars($art['judul']) ?></h4>
                                        <p class="text-sm text-gray-500 line-clamp-2"><?= htmlspecialchars(substr($art['konten'], 0, 100)) ?>...</p>
                                        <div class="flex justify-between items-center mt-2">
                                            <span class="text-xs text-gray-400">
                                                Oleh: <?= htmlspecialchars($art['penulis']) ?> | 
                                                <?= date('d M Y', strtotime($art['tanggal_publish'])) ?>
                                            </span>
                                            <div>
                                                <a href="editArtikel.php?id=<?= $art['artikel_id'] ?>" class="text-blue-500 hover:text-blue-700 mr-2">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="hapusArtikel.php?id=<?= $art['artikel_id'] ?>" 
                                                   onclick="return confirm('Yakin hapus artikel ini?')" 
                                                   class="text-red-500 hover:text-red-700">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                </div>

                <!-- Product Management -->
                <div class="bg-white rounded-lg shadow overflow-hidden mb-6">
                    <div class="p-4 border-b flex justify-between items-center">
                        <h3 class="font-bold text-lg">Manajemen Produk</h3>
                        <a href="tambahProduk.php" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                            <i class="fas fa-plus mr-1"></i> Tambah Produk
                        </a>
                    </div>
                    
                    <div class="p-4 border-b">
                        <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                            <form method="get" action="dashboard.php" class="mb-3 md:mb-0">
                                <div class="flex">
                                    <input type="text" name="search" 
                                           class="border border-gray-300 rounded-l px-3 py-1 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                                           placeholder="Cari produk..." 
                                           value="<?= htmlspecialchars($search) ?>">
                                    <button type="submit" class="bg-blue-500 text-white px-3 py-1 rounded-r hover:bg-blue-600">
                                        <i class="fas fa-search"></i>
                                    </button>
                                </div>
                            </form>
                            <div class="flex items-center">
                                <span class="text-sm text-gray-500 mr-2">Item per halaman:</span>
                                <select class="border border-gray-300 rounded px-2 py-1 text-sm per-page-select">
                                    <option value="10" selected>10</option>
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Produk</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stok</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gambar</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php 
                                if(mysqli_num_rows($result_produk) > 0) {
                                    $no = ($current_page - 1) * $per_page + 1;
                                    while($row = mysqli_fetch_assoc($result_produk)): 
                                        $has_discount = !empty($row['harga_diskon']) && $row['harga_diskon'] < $row['harga'];
                                ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $no++ ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($row['nama_produk']) ?></div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <?php if($has_discount): ?>
                                                <span class="line-through text-gray-500 mr-1">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                                <span class="text-red-600 font-bold">Rp <?= number_format($row['harga_diskon'], 0, ',', '.') ?></span>
                                            <?php else: ?>
                                                Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= $row['stok'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <?php if(!empty($row['gambar_produk'])): ?>
                                            <img src="../Uploads/<?= htmlspecialchars($row['gambar_produk']) ?>" class="w-10 h-10 object-cover rounded">
                                        <?php else: ?>
                                            <span class="text-gray-400">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $row['stok'] > 0 ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' ?>">
                                            <?= $row['stok'] > 0 ? 'Aktif' : 'Nonaktif' ?>
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <a href="edit.php?id=<?= $row['produk_id'] ?>" class="text-yellow-600 hover:text-yellow-900 mr-3">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="hapus.php?id=<?= $row['produk_id'] ?>" 
                                           onclick="return confirm('Hapus produk ini akan menghapus semua data terkait. Lanjutkan?')" 
                                           class="text-red-600 hover:text-red-900">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php 
                                    endwhile;
                                } else {
                                    echo '<tr><td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada produk ditemukan</td></tr>';
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
                        <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p class="text-sm text-gray-700">
                                    Menampilkan halaman <span class="font-medium"><?= $current_page ?></span> dari <span class="font-medium"><?= $total_pages ?></span>
                                </p>
                            </div>
                            <div>
                                <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                                    <?php if($current_page > 1): ?>
                                        <a href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Previous</span>
                                            <i class="fas fa-chevron-left"></i>
                                        </a>
                                    <?php endif; ?>
                                    
                                    <?php if($current_page < $total_pages): ?>
                                        <a href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
                                            <span class="sr-only">Next</span>
                                            <i class="fas fa-chevron-right"></i>
                                        </a>
                                    <?php endif; ?>
                                </nav>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Sales Chart
    const salesCtx = document.getElementById('salesChart').getContext('2d');
    const salesChart = new Chart(salesCtx, {
        type: 'line',
        data: {
            labels: <?= json_encode($sales_labels) ?>,
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: <?= json_encode($sales_data) ?>,
                backgroundColor: 'rgba(59, 130, 246, 0.05)',
                borderColor: 'rgba(59, 130, 246, 1)',
                borderWidth: 2,
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Rp ' + context.raw.toLocaleString();
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                }
            }
        }
    });

    // Handle per page change
    document.querySelector('.per-page-select').addEventListener('change', function() {
        window.location.href = '?per_page=' + this.value + '&search=<?= urlencode($search) ?>';
    });
    </script>
</body>
</html>