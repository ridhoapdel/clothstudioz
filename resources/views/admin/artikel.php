<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Search functionality
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query artikel
$query = "SELECT * FROM artikel";
if (!empty($search)) {
    $query .= " WHERE judul LIKE '%$search%' OR penulis LIKE '%$search%'";
}
$query .= " ORDER BY tanggal_publish DESC";

$result = mysqli_query($conn, $query);

// Pagination
$per_page = 10;
$total_results = mysqli_num_rows($result);
$total_pages = ceil($total_results / $per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $per_page;

$query .= " LIMIT $offset, $per_page";
$result = mysqli_query($conn, $query);

include '../layout/header.php';
include '../layout/sidebar.php';
?>

<div class="ml-64 p-6">
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <div class="p-4 border-b flex justify-between items-center">
            <h1 class="text-xl font-bold">Manajemen Artikel</h1>
            <a href="tambah_artikel.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md text-sm">
                <i class="fas fa-plus mr-1"></i> Tambah Artikel
            </a>
        </div>
        
        <div class="p-4 border-b bg-gray-50">
            <form method="get" class="flex">
                <input type="text" name="search" placeholder="Cari judul atau penulis..." 
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
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penulis</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Gambar</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php if(mysqli_num_rows($result) > 0): ?>
                        <?php $no = $offset + 1; while($row = mysqli_fetch_assoc($result)): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="px-6 py-4 whitespace-nowrap"><?= $no++ ?></td>
                            <td class="px-6 py-4">
                                <div class="font-medium"><?= htmlspecialchars($row['judul']) ?></div>
                                <div class="text-sm text-gray-500 line-clamp-2"><?= substr(htmlspecialchars($row['konten']), 0, 100) ?>...</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['penulis']) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= date('d M Y', strtotime($row['tanggal_publish'])) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <?php if(!empty($row['gambar'])): ?>
                                    <img src="../uploads/<?= $row['gambar'] ?>" class="w-16 h-16 object-cover rounded">
                                <?php else: ?>
                                    <span class="text-gray-400">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="edit_artikel.php?id=<?= $row['artikel_id'] ?>" class="text-yellow-500 hover:text-yellow-700 mr-3">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="hapus_artikel.php?id=<?= $row['artikel_id'] ?>" 
                                   onclick="return confirm('Yakin hapus artikel ini?')"
                                   class="text-red-500 hover:text-red-700">
                                    <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada artikel ditemukan</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 border-t flex items-center justify-between">
            <div class="text-sm text-gray-700">
                Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $per_page, $total_results) ?> dari <?= $total_results ?> artikel
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

<?php include '../layout/footer.php'; ?>