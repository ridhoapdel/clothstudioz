<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Filter
$kategori = isset($_GET['kategori']) ? (int)$_GET['kategori'] : 0;
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'terlaris';

// Query kategori
$kategories = mysqli_query($conn, "SELECT * FROM kategori");

// Query produk
$query = "SELECT 
            p.produk_id,
            p.nama_produk,
            k.nama_kategori,
            p.harga,
            p.stok,
            COALESCE(SUM(it.jumlah), 0) as total_terjual,
            COALESCE(SUM(it.subtotal), 0) as total_penjualan
          FROM produk p
          LEFT JOIN kategori k ON p.kategori_id = k.kategori_id
          LEFT JOIN item_transaksi it ON p.produk_id = it.produk_id
          LEFT JOIN transaksi t ON it.transaksi_id = t.transaksi_id AND t.status = 'completed'";

if ($kategori > 0) {
    $query .= " WHERE p.kategori_id = $kategori";
}

$query .= " GROUP BY p.produk_id";

if ($sort == 'terlaris') {
    $query .= " ORDER BY total_terjual DESC";
} elseif ($sort == 'penjualan') {
    $query .= " ORDER BY total_penjualan DESC";
} else {
    $query .= " ORDER BY p.nama_produk ASC";
}

$produk = mysqli_query($conn, $query);

include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="ml-64 p-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b">
            <h1 class="text-xl font-bold">Laporan Produk</h1>
        </div>
        
        <div class="p-4 border-b bg-gray-50">
            <form method="get" class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Kategori</label>
                    <select name="kategori" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="0">Semua Kategori</option>
                        <?php while($cat = mysqli_fetch_assoc($kategories)): ?>
                            <option value="<?= $cat['kategori_id'] ?>" <?= $kategori == $cat['kategori_id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat['nama_kategori']) ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Urutkan Berdasarkan</label>
                    <select name="sort" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="terlaris" <?= $sort == 'terlaris' ? 'selected' : '' ?>>Terlaris</option>
                        <option value="penjualan" <?= $sort == 'penjualan' ? 'selected' : '' ?>>Total Penjualan</option>
                        <option value="nama" <?= $sort == 'nama' ? 'selected' : '' ?>>Nama Produk</option>
                    </select>
                </div>
                <div class="flex items-end">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Filter
                    </button>
                    <a href="laporan_produk.php" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                        Reset
                    </a>
                </div>
            </form>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kategori</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Terjual</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Penjualan</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(mysqli_num_rows($produk) > 0): ?>
                        <?php while($row = mysqli_fetch_assoc($produk)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center">
                                    <?php if(!empty($row['gambar_produk'])): ?>
                                        <img src="../uploads/<?= $row['gambar_produk'] ?>" class="w-10 h-10 object-cover rounded mr-3">
                                    <?php endif; ?>
                                    <div>
                                        <div class="font-medium"><?= htmlspecialchars($row['nama_produk']) ?></div>
                                        <div class="text-sm text-gray-500">ID: <?= $row['produk_id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['nama_kategori']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['stok'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $row['total_terjual'] ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($row['total_penjualan'], 0, ',', '.') ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada produk ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../layout/footer.php'; ?>