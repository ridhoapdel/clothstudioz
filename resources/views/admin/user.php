<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Handle search
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';

// Query user
$query = "SELECT * FROM user WHERE role = 'pelanggan'";

if (!empty($search)) {
    $query .= " AND (username LIKE '%$search%' OR email LIKE '%$search%')";
}

$query .= " ORDER BY created_at DESC";
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
    <title>Manajemen User - Laviede</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include 'layout/sidebar.php'; ?>
    
    <div class="ml-64 p-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold">Manajemen User</h1>
            </div>
            
            <div class="p-4 border-b">
                <form method="get" class="flex">
                    <input type="text" name="search" placeholder="Cari username atau email" 
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
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">User</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Email</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Bergabung</th>
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
                                    <div class="flex items-center">
                                        <div class="flex-shrink-0 h-10 w-10 rounded-full overflow-hidden">
                                            <?php if(!empty($row['profile_picture'])): ?>
                                                <img src="../uploads/<?= $row['profile_picture'] ?>" class="h-full w-full object-cover">
                                            <?php else: ?>
                                                <div class="h-full w-full bg-gray-200 flex items-center justify-center text-gray-400">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                        <div class="ml-4">
                                            <div class="font-medium"><?= htmlspecialchars($row['username']) ?></div>
                                            <div class="text-sm text-gray-500">Pelanggan</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= htmlspecialchars($row['email']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap"><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full bg-green-100 text-green-800">
                                        Aktif
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <a href="user_detail.php?id=<?= $row['user_id'] ?>" class="text-blue-500 hover:text-blue-700 mr-3">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="user_hapus.php?id=<?= $row['user_id'] ?>" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')"
                                       class="text-red-500 hover:text-red-700">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">Tidak ada user ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $per_page, $total_results) ?> dari <?= $total_results ?> user
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
</body>
</html>