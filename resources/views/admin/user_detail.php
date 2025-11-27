<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($user_id <= 0) {
    header("Location: user.php");
    exit;
}

// Ambil data user
$user = mysqli_query($conn, "SELECT * FROM user WHERE user_id = $user_id")->fetch_assoc();
if (!$user) {
    header("Location: user.php");
    exit;
}

// Ambil wishlist user
$wishlist = mysqli_query($conn, "
    SELECT w.*, p.nama_produk, p.harga, p.gambar_produk 
    FROM wishlist w
    JOIN produk p ON w.produk_id = p.produk_id
    WHERE w.user_id = $user_id
    ORDER BY w.tanggal_ditambahkan DESC
");

// Ambil keranjang user
$keranjang = mysqli_query($conn, "
    SELECT k.*, p.nama_produk, p.harga, p.gambar_produk, pv.ukuran
    FROM keranjang k
    JOIN produk p ON k.produk_id = p.produk_id
    LEFT JOIN produk_variasi pv ON (k.size = pv.ukuran AND k.produk_id = pv.produk_id)
    WHERE k.user_id = $user_id
    ORDER BY k.tanggal_ditambahkan DESC
");

// Ambil transaksi user
$transaksi = mysqli_query($conn, "
    SELECT t.*, k.nama_kurir, k.biaya_pengiriman, k.estimasi_pengiriman,
           (SELECT COUNT(*) FROM item_transaksi it WHERE it.transaksi_id = t.transaksi_id) as jumlah_item
    FROM transaksi t
    LEFT JOIN kurir k ON t.kurir_id = k.kurir_id
    WHERE t.user_id = $user_id
    ORDER BY t.tanggal_transaksi DESC
");

include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="ml-64 p-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h1 class="text-xl font-bold">Detail User: <?= htmlspecialchars($user['username']) ?></h1>
            <a href="user.php" class="text-blue-500 hover:text-blue-700">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-4 gap-0 border-b">
            <!-- Profil User -->
            <div class="p-6 border-r">
                <div class="flex flex-col items-center">
                    <div class="h-24 w-24 rounded-full overflow-hidden mb-4">
                        <?php if(!empty($user['profile_picture'])): ?>
                            <img src="../uploads/<?= $user['profile_picture'] ?>" class="h-full w-full object-cover">
                        <?php else: ?>
                            <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-500 text-4xl">
                                <i class="fas fa-user"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    <h3 class="text-lg font-medium"><?= htmlspecialchars($user['username']) ?></h3>
                    <p class="text-gray-500 mb-2"><?= htmlspecialchars($user['email']) ?></p>
                    <span class="px-3 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">
                        Pelanggan
                    </span>
                    <p class="text-sm text-gray-500 mt-4">
                        Bergabung sejak: <?= date('d M Y', strtotime($user['created_at'])) ?>
                    </p>
                </div>
            </div>
            
            <!-- Wishlist -->
            <div class="p-4 border-r">
                <h4 class="font-medium mb-3">
                    <i class="fas fa-heart text-red-500 mr-2"></i> Wishlist
                    <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5 ml-1"><?= mysqli_num_rows($wishlist) ?></span>
                </h4>
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    <?php if(mysqli_num_rows($wishlist) > 0): ?>
                        <?php while($item = mysqli_fetch_assoc($wishlist)): ?>
                        <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <div class="flex-shrink-0 h-10 w-10 rounded overflow-hidden">
                                <img src="../uploads/<?= $item['gambar_produk'] ?>" class="h-full w-full object-cover">
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
            <div class="p-4">
                <h4 class="font-medium mb-3">
                    <i class="fas fa-shopping-cart text-blue-500 mr-2"></i> Keranjang
                    <span class="text-xs bg-gray-200 rounded-full px-2 py-0.5 ml-1"><?= mysqli_num_rows($keranjang) ?></span>
                </h4>
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    <?php if(mysqli_num_rows($keranjang) > 0): ?>
                        <?php while($item = mysqli_fetch_assoc($keranjang)): ?>
                        <div class="flex items-center p-2 hover:bg-gray-50 rounded">
                            <div class="flex-shrink-0 h-10 w-10 rounded overflow-hidden">
                                <img src="../uploads/<?= $item['gambar_produk'] ?>" class="h-full w-full object-cover">
                            </div>
                            <div class="ml-3">
                                <p class="text-sm font-medium"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                <p class="text-xs text-gray-500">
                                    Rp <?= number_format($item['harga'], 0, ',', '.') ?> 
                                    <?= $item['ukuran'] ? "| Size: {$item['ukuran']}" : '' ?>
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
            <div class="p-4 border-t md:border-t-0 md:border-r">
                <h4 class="font-medium mb-3">
                    <i class="fas fa-question-circle text-purple-500 mr-2"></i> Help Center
                </h4>
                <div class="space-y-3 max-h-60 overflow-y-auto">
                    <?php 
                    $help = mysqli_query($conn, "SELECT * FROM help_center WHERE user_id = $user_id ORDER BY created_at DESC");
                    if(mysqli_num_rows($help) > 0): ?>
                        <?php while($item = mysqli_fetch_assoc($help)): ?>
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
        
        <!-- Transaksi -->
        <div class="p-4 border-t">
            <h4 class="font-medium mb-3">
                <i class="fas fa-receipt text-green-500 mr-2"></i> Riwayat Transaksi
            </h4>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kurir</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if(mysqli_num_rows($transaksi) > 0): ?>
                            <?php while($trans = mysqli_fetch_assoc($transaksi)): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap font-medium"><?= $trans['kode_transaksi'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('d M Y H:i', strtotime($trans['tanggal_transaksi'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($trans['total'], 0, ',', '.') ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full 
                                        <?= $trans['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                           ($trans['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                           'bg-blue-100 text-blue-800') ?>">
                                        <?= ucfirst($trans['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= ucfirst($trans['metode_pembayaran']) ?>
                                    <span class="block text-xs <?= $trans['status_pembayaran'] == 'paid' ? 'text-green-600' : 'text-yellow-600' ?>">
                                        <?= ucfirst($trans['status_pembayaran']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= $trans['nama_kurir'] ?? '-' ?>
                                    <?php if($trans['nomor_resi']): ?>
                                        <span class="block text-xs">Resi: <?= $trans['nomor_resi'] ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="transaksi_detail.php?id=<?= $trans['transaksi_id'] ?>" 
                                       class="text-blue-500 hover:text-blue-700">
                                        <i class="fas fa-eye"></i> Detail
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Belum ada transaksi</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>