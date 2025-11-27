<?php
session_start();
if (!isset($_SESSION['admin']) && !isset($_SESSION['kurir'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Handle status update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $transaksi_id = (int)$_POST['transaksi_id'];
    $new_status = $_POST['status'];
    $nomor_resi = $_POST['nomor_resi'] ?? null;
    $tracking_status = $_POST['tracking_status'] ?? null;

    // Ambil data transaksi saat ini
    $stmt = $conn->prepare("SELECT status, status_pembayaran FROM transaksi WHERE transaksi_id = ?");
    $stmt->bind_param("i", $transaksi_id);
    $stmt->execute();
    $current_transaction = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Validasi alur status berdasarkan role
    $valid = true;
    $error_message = "";
    
    if (isset($_SESSION['admin'])) {
        // Admin hanya bisa mengubah dari pending ke dikemas
        if ($current_transaction['status'] == 'pending' && $new_status == 'dikemas') {
            // Pastikan pembayaran sudah diverifikasi
            if ($current_transaction['status_pembayaran'] != 'paid') {
                $valid = false;
                $error_message = "Pembayaran belum diverifikasi, tidak bisa mengubah status ke Dikemas";
            }
        } else {
            $valid = false;
            $error_message = "Admin hanya bisa mengubah status dari Pending ke Dikemas";
        }
    } 
    
    if (isset($_SESSION['kurir'])) {
        // Kurir hanya bisa mengubah dari dikemas ke dikirim atau dikirim ke completed
        if (!(($current_transaction['status'] == 'dikemas' && $new_status == 'dikirim') || 
              ($current_transaction['status'] == 'dikirim' && $new_status == 'completed'))) {
            $valid = false;
            $error_message = "Kurir hanya bisa mengubah status dari Dikemas ke Dikirim atau Dikirim ke Completed";
        }
        
        // Untuk status dikirim, wajib ada nomor resi dan tracking status
        if ($new_status == 'dikirim' && (empty($nomor_resi) || empty($tracking_status))) {
            $valid = false;
            $error_message = "Nomor resi dan status pelacakan wajib diisi untuk status dikirim";
        }
    }

    if ($valid) {
        // Debugging: Log nilai yang akan diupdate
        error_log("Updating transaksi_id: $transaksi_id, status: $new_status, nomor_resi: $nomor_resi, tracking_status: $tracking_status");

        $stmt = $conn->prepare("UPDATE transaksi SET status = ?, nomor_resi = ?, tracking_status = ? WHERE transaksi_id = ?");
        $stmt->bind_param("sssi", $new_status, $nomor_resi, $tracking_status, $transaksi_id);
        if ($stmt->execute()) {
            $success_message = "Status transaksi berhasil diperbarui!";
        } else {
            $error_message = "Gagal memperbarui status transaksi: " . $stmt->error;
            error_log("Update failed: " . $stmt->error);
        }
        $stmt->close();
    }
}

// Handle verifikasi bukti pembayaran
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['verify_payment'])) {
    $transaksi_id = (int)$_POST['transaksi_id'];
    
    // Cek status transaksi saat ini
    $stmt = $conn->prepare("SELECT status FROM transaksi WHERE transaksi_id = ?");
    $stmt->bind_param("i", $transaksi_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $current_status = $result->fetch_assoc()['status'];
    $stmt->close();
    
    if ($current_status != 'pending') {
        $error_message = "Hanya bisa memverifikasi pembayaran untuk transaksi dengan status Pending";
    } else {
        $stmt = $conn->prepare("UPDATE bukti_pembayaran SET diverifikasi = 1 WHERE transaksi_id = ?");
        $stmt->bind_param("i", $transaksi_id);
        if ($stmt->execute()) {
            // Update status_pembayaran dan status transaksi
            $stmt2 = $conn->prepare("UPDATE transaksi SET status_pembayaran = 'paid', status = 'dikemas' WHERE transaksi_id = ?");
            $stmt2->bind_param("i", $transaksi_id);
            $stmt2->execute();
            $stmt2->close();
            $success_message = "Bukti pembayaran berhasil diverifikasi! Status transaksi diubah ke 'Dikemas'.";
        } else {
            $error_message = "Gagal memverifikasi bukti pembayaran.";
        }
        $stmt->close();
    }
}

// Filter
$status = isset($_GET['status']) ? mysqli_real_escape_string($conn, $_GET['status']) : '';
$search = isset($_GET['search']) ? mysqli_real_escape_string($conn, $_GET['search']) : '';
$month = isset($_GET['month']) ? (int)$_GET['month'] : '';
$year = isset($_GET['year']) ? (int)$_GET['year'] : '';

// Query transaksi dengan penyesuaian berdasarkan role
$query = "SELECT t.*, u.username, k.nama_ekspedisi 
          FROM transaksi t
          JOIN user u ON t.user_id = u.user_id
          LEFT JOIN ekspedisi k ON t.ekspedisi_id = k.ekspedisi_id
          WHERE 1=1";

// Filter berdasarkan role
if (isset($_SESSION['kurir'])) {
    $query .= " AND (t.status = 'dikemas' OR t.status = 'dikirim')";
}

// Filter berdasarkan status
if (isset($_SESSION['admin']) && !empty($status)) {
    $query .= " AND t.status = '$status'";
}

// Filter berdasarkan bulan dan tahun
if (!empty($month) && !empty($year)) {
    $query .= " AND MONTH(t.tanggal_transaksi) = $month AND YEAR(t.tanggal_transaksi) = $year";
} elseif (!empty($month)) {
    $query .= " AND MONTH(t.tanggal_transaksi) = $month";
} elseif (!empty($year)) {
    $query .= " AND YEAR(t.tanggal_transaksi) = $year";
}

// Filter berdasarkan pencarian
if (!empty($search)) {
    $query .= " AND (t.kode_transaksi LIKE '%$search%' OR u.username LIKE '%$search%')";
}

$query .= " ORDER BY t.tanggal_transaksi DESC";
$result = mysqli_query($conn, $query);

// Pagination
$per_page = 10;
$total_results = mysqli_num_rows($result);
$total_pages = ceil($total_results / $per_page);
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $per_page;

$query .= " LIMIT $offset, $per_page";
$result = mysqli_query($conn, $query);

// Ambil data bukti pembayaran untuk setiap transaksi
$payment_proofs = [];
while ($row = mysqli_fetch_assoc($result)) {
    $stmt = $conn->prepare("SELECT file_bukti, diverifikasi FROM bukti_pembayaran WHERE transaksi_id = ?");
    $stmt->bind_param("i", $row['transaksi_id']);
    $stmt->execute();
    $proof_result = $stmt->get_result();
    if ($proof_result->num_rows > 0) {
        $payment_proofs[$row['transaksi_id']] = $proof_result->fetch_assoc();
    }
    $stmt->close();
}
mysqli_data_seek($result, 0); // Reset pointer untuk loop berikutnya

// Definisikan pemetaan kelas CSS untuk status
$status_classes = [
    'completed' => 'bg-green-100 text-green-800',
    'pending' => 'bg-yellow-100 text-yellow-800',
    'cancelled' => 'bg-red-100 text-red-800',
    'dikemas' => 'bg-blue-100 text-blue-800',
    'dikirim' => 'bg-purple-100 text-purple-800'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Transaksi - Laviede</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        .modal-content {
            background-color: white;
            padding: 2rem;
            border-radius: 0.5rem;
            width: 90%;
            max-width: 500px;
        }
    </style>
</head>
<body class="bg-gray-100">
    <?php 
    if (isset($_SESSION['admin'])) {
        include 'layout/sidebar.php'; 
    } else {
        include 'layout/sidebar_kurir.php';
    }
    ?>
    
    <div class="ml-64 p-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b">
                <h1 class="text-xl font-bold">
                    <?= isset($_SESSION['admin']) ? 'Manajemen Transaksi' : 'Daftar Pengiriman' ?>
                </h1>
            </div>
            
            <?php if (isset($success_message)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-2 rounded m-4" role="alert">
                    <?php echo htmlspecialchars($success_message); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error_message)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded m-4" role="alert">
                    <?php echo htmlspecialchars($error_message); ?>
                </div>
            <?php endif; ?>

            <div class="p-4 border-b bg-gray-50">
                <form method="get" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <?php if (isset($_SESSION['admin'])): ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                        <select name="status" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="">Semua Status</option>
                            <option value="pending" <?= $status == 'pending' ? 'selected' : '' ?>>Pending</option>
                            <option value="dikemas" <?= $status == 'dikemas' ? 'selected' : '' ?>>Dikemas</option>
                            <option value="dikirim" <?= $status == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                            <option value="completed" <?= $status == 'completed' ? 'selected' : '' ?>>Selesai</option>
                            <option value="cancelled" <?= $status == 'cancelled' ? 'selected' : '' ?>>Dibatalkan</option>
                        </select>
                    </div>
                    <?php endif; ?>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                        <select name="month" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="">Semua Bulan</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?= $i ?>" <?= isset($_GET['month']) && $_GET['month'] == $i ? 'selected' : '' ?>>
                                    <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                        <select name="year" class="border border-gray-300 rounded px-3 py-2 w-full">
                            <option value="">Semua Tahun</option>
                            <?php for ($i = date('Y'); $i >= date('Y') - 5; $i--): ?>
                                <option value="<?= $i ?>" <?= isset($_GET['year']) && $_GET['year'] == $i ? 'selected' : '' ?>>
                                    <?= $i ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cari</label>
                        <div class="flex">
                            <input type="text" name="search" placeholder="Kode transaksi atau username" 
                                   class="border border-gray-300 rounded-l px-3 py-2 w-full focus:outline-none focus:ring-2 focus:ring-blue-500"
                                   value="<?= htmlspecialchars($search) ?>">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-r hover:bg-blue-600">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                    <div class="flex items-end">
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                            Filter
                        </button>
                        <a href="transaksi.php" class="ml-2 bg-gray-200 text-gray-700 px-4 py-2 rounded hover:bg-gray-300">
                            Reset
                        </a>
                    </div>
                </form>
            </div>
            
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Customer</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pembayaran</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Ekspedisi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Pelacakan</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php if (mysqli_num_rows($result) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                            <tr class="hover:bg-gray-50">
                                <td class="px-6 py-4 whitespace-nowrap font-medium">
                                    <?= htmlspecialchars($row['kode_transaksi'] ?? '-') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= htmlspecialchars($row['username']) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= date('d M Y H:i', strtotime($row['tanggal_transaksi'])) ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    Rp <?= number_format($row['total'], 0, ',', '.') ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs rounded-full <?= isset($status_classes[$row['status']]) ? $status_classes[$row['status']] : 'bg-gray-100 text-gray-800' ?>">
                                        <?= ucfirst($row['status']) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <?= ucfirst($row['metode_pembayaran']) ?>
                                    <span class="block text-xs <?= $row['status_pembayaran'] == 'paid' ? 'text-green-600' : 'text-yellow-600' ?>">
                                        <?= ucfirst($row['status_pembayaran']) ?>
                                        <?php if ($row['status_pembayaran'] == 'paid'): ?>
                                            (Terverifikasi)
                                        <?php endif; ?>
                                    </span>
                                    <?php if (isset($payment_proofs[$row['transaksi_id']])): ?>
                                        <a href="../Uploads/payments/<?php echo htmlspecialchars($payment_proofs[$row['transaksi_id']]['file_bukti']) ?>" 
                                           target="_blank" class="text-blue-500 text-xs hover:underline">Lihat Bukti</a>
                                        <?php if ($payment_proofs[$row['transaksi_id']]['diverifikasi'] == 0 && $row['status_pembayaran'] == 'pending' && isset($_SESSION['admin'])): ?>
                                            <form method="POST" class="inline">
                                                <input type="hidden" name="transaksi_id" value="<?= $row['transaksi_id'] ?>">
                                                <button type="submit" name="verify_payment" class="text-green-500 text-xs hover:underline">Verifikasi</button>
                                            </form>
                                        <?php endif; ?>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?= htmlspecialchars($row['nama_ekspedisi'] ?? '-') ?>
                                    <?php if ($row['nomor_resi']): ?>
                                        <span class="block text-xs">Resi: <?= htmlspecialchars($row['nomor_resi']) ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <?= isset($row['tracking_status']) && $row['tracking_status'] !== null ? htmlspecialchars($row['tracking_status']) : '-' ?>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <a href="transaksi_detail.php?id=<?= $row['transaksi_id'] ?>" 
                                       class="text-blue-500 hover:text-blue-600 mr-2">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <?php if ((isset($_SESSION['admin']) && $row['status'] == 'pending') || 
                                              (isset($_SESSION['kurir']) && ($row['status'] == 'dikemas' || $row['status'] == 'dikirim'))): ?>
                                    <button onclick="openModal(<?= $row['transaksi_id'] ?>, '<?= $row['status'] ?>', '<?= htmlspecialchars($row['nomor_resi'] ?? '') ?>', '<?= htmlspecialchars($row['tracking_status'] ?? '') ?>')" 
                                            class="text-yellow-500 hover:text-yellow-600">
                                        <i class="fas fa-edit"></i>
                                    </button>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="9" class="px-6 py-4 text-center text-gray-500">Tidak ada transaksi ditemukan.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="px-4 py-3 border-t flex items-center justify-between">
                <div class="text-sm text-gray-700">
                    Menampilkan <?= $offset + 1 ?> sampai <?= min($offset + $per_page, $total_results) ?> dari <?= $total_results ?> transaksi
                </div>
                <div class="flex space-x-2">
                    <?php if ($current_page > 1): ?>
                        <a href="?page=<?= $current_page - 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&month=<?= $month ?>&year=<?= $year ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?= $i ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&month=<?= $month ?>&year=<?= $year ?>" 
                           class="px-3 py-1 border rounded <?= $i == $current_page ? 'bg-blue-500 text-white' : 'hover:bg-gray-100' ?>">
                            <?= $i ?>
                        </a>
                    <?php endfor; ?>
                    
                    <?php if ($current_page < $total_pages): ?>
                        <a href="?page=<?= $current_page + 1 ?>&status=<?= $status ?>&search=<?= urlencode($search) ?>&month=<?= $month ?>&year=<?= $year ?>" 
                           class="px-3 py-1 border rounded hover:bg-gray-100">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal untuk Update Status -->
    <div id="statusModal" class="modal">
        <div class="modal-content">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold">Update Status Transaksi</h2>
                <button onclick="closeModal()" class="text-gray-600 hover:text-gray-900">
                    <i class="fa fa-times"></i>
                </button>
            </div>
            <form id="statusForm" method="POST">
                <input type="hidden" name="transaksi_id" id="modalTransaksiId">
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="status" id="statusSelect" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <!-- Options akan diisi oleh JavaScript -->
                    </select>
                </div>
                <div id="resiField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700">Nomor Resi</label>
                    <input type="text" name="nomor_resi" id="nomorResi" class="border border-gray-300 rounded px-3 py-2 w-full" placeholder="Masukkan nomor resi">
                </div>
                <div id="trackingField" class="mb-4 hidden">
                    <label class="block text-sm font-medium text-gray-700">Status Pelacakan</label>
                    <select name="tracking_status" id="trackingStatus" class="border border-gray-300 rounded px-3 py-2 w-full">
                        <option value="" disabled selected>Pilih Status Pelacakan</option>
                        <option value="Menunggu Pengambilan">Menunggu Pengambilan</option>
                        <option value="Dalam Perjalanan ke Sortir">Dalam Perjalanan ke Sortir</option>
                        <option value="Tiba di Sortir">Tiba di Sortir</option>
                        <option value="Dalam Pengiriman">Dalam Pengiriman</option>
                        <option value="Tiba di Tujuan">Tiba di Tujuan</option>
                    </select>
                </div>
                <div class="flex justify-end space-x-2">
                    <button type="button" onclick="closeModal()" class="px-4 py-2 border rounded-lg hover:bg-gray-100">Batal</button>
                    <button type="submit" name="update_status" class="px-4 py-2 bg-blue-500 text-white rounded-lg hover:bg-blue-600">Simpan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openModal(transaksiId, currentStatus, nomorResi, trackingStatus) {
            document.getElementById('modalTransaksiId').value = transaksiId;
            document.getElementById('statusSelect').value = currentStatus;
            document.getElementById('nomorResi').value = nomorResi || '';
            document.getElementById('trackingStatus').value = trackingStatus || '';
            document.getElementById('statusModal').style.display = 'flex';
            
            // Dapatkan role (admin atau kurir)
            const isAdmin = <?= isset($_SESSION['admin']) ? 'true' : 'false' ?>;
            const isKurir = <?= isset($_SESSION['kurir']) ? 'true' : 'false' ?>;
            
            const statusSelect = document.getElementById('statusSelect');
            
            // Reset options
            statusSelect.innerHTML = '';
            
            if (isAdmin) {
                // Admin hanya bisa mengubah dari pending ke dikemas
                if (currentStatus === 'pending') {
                    statusSelect.innerHTML = `
                        <option value="pending" selected>Pending</option>
                        <option value="dikemas">Dikemas</option>
                    `;
                } else {
                    // Admin tidak bisa mengubah status selain dari pending
                    statusSelect.innerHTML = `<option value="${currentStatus}" selected>${currentStatus}</option>`;
                    statusSelect.disabled = true;
                }
            }
            
            if (isKurir) {
                // Kurir hanya bisa mengubah dari dikemas ke dikirim atau dikirim ke completed
                if (currentStatus === 'dikemas') {
                    statusSelect.innerHTML = `
                        <option value="dikemas" selected>Dikemas</option>
                        <option value="dikirim">Dikirim</option>
                    `;
                } else if (currentStatus === 'dikirim') {
                    statusSelect.innerHTML = `
                        <option value="dikirim" selected>Dikirim</option>
                        <option value="completed">Selesai</option>
                    `;
                } else {
                    // Kurir tidak bisa mengubah status selain itu
                    statusSelect.innerHTML = `<option value="${currentStatus}" selected>${currentStatus}</option>`;
                    statusSelect.disabled = true;
                }
            }
            
            toggleResiField();
        }

        function closeModal() {
            document.getElementById('statusModal').style.display = 'none';
            document.getElementById('statusForm').reset();
            document.getElementById('resiField').classList.add('hidden');
            document.getElementById('trackingField').classList.add('hidden');
            document.getElementById('statusSelect').disabled = false;
        }

        function toggleResiField() {
            const status = document.getElementById('statusSelect').value;
            const resiField = document.getElementById('resiField');
            const trackingField = document.getElementById('trackingField');
            if (status === 'dikirim') {
                resiField.classList.remove('hidden');
                trackingField.classList.remove('hidden');
                document.getElementById('nomorResi').required = true;
                document.getElementById('trackingStatus').required = true;
            } else {
                resiField.classList.add('hidden');
                trackingField.classList.add('hidden');
                document.getElementById('nomorResi').required = false;
                document.getElementById('trackingStatus').required = false;
            }
        }

        document.getElementById('statusSelect').addEventListener('change', toggleResiField);
    </script>
</body>
</html>