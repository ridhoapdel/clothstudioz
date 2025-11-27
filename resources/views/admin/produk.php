<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Handle search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query produk
$query = "SELECT p.*, 
                 bd.diskon_persen, 
                 bd.harga_diskon,
                 bd.mulai_diskon,
                 bd.selesai_diskon
          FROM produk p
          LEFT JOIN barang_diskon bd ON p.produk_id = bd.produk_id 
              AND CURDATE() BETWEEN bd.mulai_diskon AND bd.selesai_diskon";

if (!empty($search)) {
    $query .= " WHERE p.nama_produk LIKE '%$search%' OR p.deskripsi LIKE '%$search%'";
}

$query .= " ORDER BY p.produk_id DESC";
$result = mysqli_query($conn, $query);

// Pagination
$per_page = 10;
$total_results = mysqli_num_rows($result);
$total_pages = ceil($total_results / $per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $per_page;

$query .= " LIMIT $offset, $per_page";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Produk - Laviede</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    
    <div class="ml-64 p-6">
        <div>
            
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center">
                <h1 class="text-xl font-bold">Manajemen Produk</h1>
                <a href="tambahProduk.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                    <i class="fas fa-plus mr-1"></i> Tambah Produk
                </a>
            </div>
            
            <div class="p-4 border-b">
                <form method="get" class="flex">
                    <input type="text" name="search" placeholder="Cari produk..." 
                           class="border border-gray-300 rounded-l px-4 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                           value="<?= htmlspecialchars($search) ?>">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600">
                        <i class="fas fa-search"></i>
                    </button>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">No</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Nama Produk</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Stok</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gambar</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if(mysqli_num_rows($result) > 0): ?>
                            <?php $no = $offset + 1; while($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap"><?= $no++ ?></td>
                                <td class="px-6 py-4">
                                    <div class="font-medium"><?= htmlspecialchars($row['nama_produk']) ?></div>
                                    <div class="text-sm text-gray-500"><?= substr(htmlspecialchars($row['deskripsi']), 0, 50) ?>...</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if(!empty($row['harga_diskon'])): ?>
                                        <span class="line-through text-gray-400">Rp <?= number_format($row['harga'], 0, ',', '.') ?></span>
                                        <span class="text-red-600 font-bold">Rp <?= number_format($row['harga_diskon'], 0, ',', '.') ?></span>
                                    <?php else: ?>
                                        Rp <?= number_format($row['harga'], 0, ',', '.') ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= $row['stok'] ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?php if(!empty($row['gambar_produk'])): ?>
                                        <img src="../uploads/<?= $row['gambar_produk'] ?>" class="w-12 h-12 object-cover rounded">
                                    <?php else: ?>
                                        <div class="w-12 h-12 bg-gray-200 rounded flex items-center justify-center text-gray-400">
                                            <i class="fas fa-image"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?= $row['stok'] > 0 ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                                        <?= $row['stok'] > 0 ? 'Tersedia' : 'Habis' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="editProduk.php?id=<?= $row['produk_id'] ?>" class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="hapusProduk.php?id=<?= $row['produk_id'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?')"
                                       class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">Tidak ada produk ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $per_page, $total_results) ?> dari <?= $total_results ?> produk
                </div>
                <div class="flex space-x-2">
                    <?php if($current_page > 1): ?>
                        <a href="?page=<?= $current_page - 1 ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-1 border rounded <?= $i == $current_page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if($current_page < $total_pages): ?>
                        <a href="?page=<?= $current_page + 1 ?>&search=<?= urlencode($search) ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        </div>
    </div>
</body>
</html>