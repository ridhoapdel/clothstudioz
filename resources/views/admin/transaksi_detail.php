<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

$transaksi_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($transaksi_id <= 0) {
    header("Location: transaksi.php");
    exit;
}

// Ambil data transaksi
$stmt = $conn->prepare("
    SELECT t.*, u.username, u.email, k.nama_ekspedisi, k.estimasi_pengiriman, k.biaya_pengiriman
    FROM transaksi t
    JOIN user u ON t.user_id = u.user_id
    LEFT JOIN ekspedisi k ON t.ekspedisi_id = k.ekspedisi_id
    WHERE t.transaksi_id = ?
");
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$transaksi = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$transaksi) {
    header("Location: transaksi.php");
    exit;
}

// Ambil item transaksi
$stmt = $conn->prepare("
    SELECT it.*, p.nama_produk, p.gambar_produk, p.harga
    FROM item_transaksi it
    JOIN produk p ON it.produk_id = p.produk_id
    WHERE it.transaksi_id = ?
");
$stmt->bind_param("i", $transaksi_id);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Proses pembaruan status dan nomor resi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $status = $_POST['status'] ?? '';
    $nomor_resi = $_POST['nomor_resi'] ?? '';

    if ($status === 'dikirim' && empty($nomor_resi)) {
        $error = "Nomor resi wajib diisi untuk status Dikirim.";
    } elseif (!in_array($status, ['pending', 'dikemas', 'dikirim'])) {
        $error = "Status tidak valid.";
    } else {
        $stmt = $conn->prepare("UPDATE transaksi SET status = ?, nomor_resi = ? WHERE transaksi_id = ?");
        $stmt->bind_param("ssi", $status, $nomor_resi, $transaksi_id);
        if ($stmt->execute()) {
            $success = "Status dan nomor resi berhasil diperbarui.";
            header("Location: transaksi_detail.php?id=$transaksi_id");
            exit;
        } else {
            $error = "Gagal memperbarui status.";
        }
        $stmt->close();
    }
}

// Proses konfirmasi atau tolak transaksi
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['konfirmasi'])) {
    $stmt = $conn->prepare("UPDATE transaksi SET status = 'dikemas' WHERE transaksi_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $transaksi_id);
    if ($stmt->execute()) {
        $success = "Transaksi berhasil dikonfirmasi.";
        header("Location: transaksi_detail.php?id=$transaksi_id");
        exit;
    } else {
        $error = "Gagal mengkonfirmasi transaksi.";
    }
    $stmt->close();
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['tolak'])) {
    $stmt = $conn->prepare("UPDATE transaksi SET status = 'cancelled' WHERE transaksi_id = ? AND status = 'pending'");
    $stmt->bind_param("i", $transaksi_id);
    if ($stmt->execute()) {
        $success = "Transaksi berhasil ditolak.";
        header("Location: transaksi_detail.php?id=$transaksi_id");
        exit;
    } else {
        $error = "Gagal menolak transaksi.";
    }
    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Transaksi - Laviede</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-gray-100">
    <?php include '../layout/sidebar.php'; ?>

    <div class="ml-64 p-6">
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <div class="p-4 border-b flex justify-between items-center">
                <h1 class="text-xl font-bold">Detail Transaksi #<?= htmlspecialchars($transaksi['kode_transaksi']) ?></h1>
                <a href="transaksi.php" class="text-blue-500 hover:text-blue-700">
                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                </a>
            </div>

            <?php if (isset($success)): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded m-6">
                    <?php echo htmlspecialchars($success); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded m-6">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">
                <!-- Info Transaksi -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">Informasi Transaksi</h2>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-500">Kode Transaksi</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['kode_transaksi']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Tanggal</p>
                            <p class="font-medium"><?= date('d M Y H:i', strtotime($transaksi['tanggal_transaksi'])) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status</p>
                            <p class="font-medium">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    <?= $transaksi['status'] == 'completed' ? 'bg-green-100 text-green-800' : 
                                       ($transaksi['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 
                                       ($transaksi['status'] == 'cancelled' ? 'bg-red-100 text-red-800' : 'bg-blue-100 text-blue-800')) ?>">
                                    <?= ucfirst($transaksi['status']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Info Customer -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">Informasi Customer</h2>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-500">Nama</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['username']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Email</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['email']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nama Penerima</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['nama_penerima']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">No. Telepon</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['no_telepon']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Alamat</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['alamat']) ?></p>
                        </div>
                    </div>
                </div>

                <!-- Info Pengiriman -->
                <div class="space-y-4">
                    <h2 class="text-lg font-semibold">Pengiriman & Pembayaran</h2>
                    <div class="space-y-2">
                        <div>
                            <p class="text-sm text-gray-500">Ekspedisi</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['nama_ekspedisi'] ?? '-') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Estimasi Pengiriman</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['estimasi_pengiriman'] ?? '-') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Biaya Pengiriman</p>
                            <p class="font-medium">Rp <?= number_format($transaksi['biaya_pengiriman'] ?? 0, 0, ',', '.') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Nomor Resi</p>
                            <p class="font-medium"><?= htmlspecialchars($transaksi['nomor_resi'] ?? '-') ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Metode Pembayaran</p>
                            <p class="font-medium"><?= ucfirst($transaksi['metode_pembayaran']) ?></p>
                        </div>
                        <div>
                            <p class="text-sm text-gray-500">Status Pembayaran</p>
                            <p class="font-medium">
                                <span class="<?= $transaksi['status_pembayaran'] == 'paid' ? 'text-green-600' : 'text-yellow-600' ?>">
                                    <?= ucfirst($transaksi['status_pembayaran']) ?>
                                </span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Update Status dan Nomor Resi -->
            <?php if ($transaksi['status'] != 'completed' && $transaksi['status'] != 'cancelled'): ?>
                <div class="p-6 border-t">
                    <h2 class="text-lg font-semibold mb-4">Update Status</h2>
                    <form method="POST" class="flex space-x-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                            <select name="status" class="border rounded px-3 py-2">
                                <option value="pending" <?= $transaksi['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                <option value="dikemas" <?= $transaksi['status'] == 'dikemas' ? 'selected' : '' ?>>Dikemas</option>
                                <option value="dikirim" <?= $transaksi['status'] == 'dikirim' ? 'selected' : '' ?>>Dikirim</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nomor Resi</label>
                            <input type="text" name="nomor_resi" value="<?= htmlspecialchars($transaksi['nomor_resi'] ?? '') ?>" 
                                   class="border rounded px-3 py-2 w-40" placeholder="Masukkan nomor resi">
                        </div>
                        <div class="flex items-end">
                            <button type="submit" name="update_status" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                                Update
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>

            <!-- Daftar Produk -->
            <div class="p-6 border-t">
                <h2 class="text-lg font-semibold mb-4">Daftar Produk</h2>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Produk</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Harga</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php 
                            $total_items = 0;
                            $total_harga = 0;
                            foreach ($items as $item): 
                                $total_items += $item['jumlah'];
                                $total_harga += $item['subtotal'];
                            ?>
                                <tr>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <?php if (!empty($item['gambar_produk'])): ?>
                                                <img src="../Uploads/<?= htmlspecialchars($item['gambar_produk']) ?>" class="w-10 h-10 object-cover rounded mr-3">
                                            <?php endif; ?>
                                            <div>
                                                <p class="font-medium"><?= htmlspecialchars($item['nama_produk']) ?></p>
                                                <?php if ($item['ukuran']): ?>
                                                    <p class="text-xs text-gray-500">Size: <?= htmlspecialchars($item['ukuran']) ?></p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($item['harga'], 0, ',', '.') ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap"><?= $item['jumlah'] ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($item['subtotal'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="bg-gray-50 font-semibold">
                                <td class="px-6 py-4">Total</td>
                                <td class="px-6 py-4"></td>
                                <td class="px-6 py-4"><?= $total_items ?></td>
                                <td class="px-6 py-4">Rp <?= number_format($total_harga, 0, ',', '.') ?></td>
                            </tr>
                            <tr class="bg-gray-50">
                                <td colspan="3" class="px-6 py-4 text-right">Biaya Pengiriman</td>
                                <td class="px-6 py-4">Rp <?= number_format($transaksi['biaya_pengiriman'] ?? 0, 0, ',', '.') ?></td>
                            </tr>
                            <tr class="bg-gray-100 font-semibold">
                                <td colspan="3" class="px-6 py-4 text-right">Total Pembayaran</td>
                                <td class="px-6 py-4">Rp <?= number_format($transaksi['total'], 0, ',', '.') ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tombol Konfirmasi/Tolak -->
            <?php if ($transaksi['status'] == 'pending'): ?>
                <div class="p-6 border-t flex items-end gap-4">
                    <form method="POST">
                        <button type="submit" name="konfirmasi" class="bg-black text-white py-3 px-4 rounded-lg hover:bg-gray-800 transition">
                            Konfirmasi
                        </button>
                    </form>
                    <form method="POST">
                        <button type="submit" name="tolak" class="bg-gray-200 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-300">
                            Tolak
                        </button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <?php include '../layout/footer.php'; ?>
</body>
</html>