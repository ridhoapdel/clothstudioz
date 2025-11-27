<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';

// Filter bulan dan tahun
$bulan = isset($_GET['bulan']) ? (int)$_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? (int)$_GET['tahun'] : date('Y');

// Query laporan penjualan
$query = "SELECT 
            DATE_FORMAT(t.tanggal_transaksi, '%d') as hari,
            COUNT(t.transaksi_id) as jumlah_transaksi,
            SUM(t.total) as total_penjualan
          FROM transaksi t
          WHERE 
            MONTH(t.tanggal_transaksi) = $bulan AND
            YEAR(t.tanggal_transaksi) = $tahun AND
            t.status = 'completed'
          GROUP BY hari
          ORDER BY hari";

$result = mysqli_query($conn, $query);
$data_penjualan = [];
while ($row = mysqli_fetch_assoc($result)) {
    $data_penjualan[$row['hari']] = $row;
}

// Query produk terlaris
$query_produk = "SELECT 
                p.nama_produk,
                SUM(it.jumlah) as total_terjual,
                SUM(it.subtotal) as total_penjualan
              FROM item_transaksi it
              JOIN produk p ON it.produk_id = p.produk_id
              JOIN transaksi t ON it.transaksi_id = t.transaksi_id
              WHERE 
                MONTH(t.tanggal_transaksi) = $bulan AND
                YEAR(t.tanggal_transaksi) = $tahun AND
                t.status = 'completed'
              GROUP BY p.produk_id
              ORDER BY total_terjual DESC
              LIMIT 5";

$produk_terlaris = mysqli_query($conn, $query_produk);

?>
<?php include '../layout/topbar.php'; ?>
<?php include '../layout/header.php'; ?>
<?php include '../layout/sidebar.php'; ?>
<div class="ml-64 p-6">
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        
        <div class="p-4 border-b">
            <h1 class="text-xl font-bold">Laporan Penjualan</h1>
        </div>
        
        <div class="p-4 border-b bg-gray-50">
            <form method="get" class="flex items-center space-x-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Bulan</label>
                    <select name="bulan" class="border border-gray-300 rounded px-3 py-2">
                        <?php for($i = 1; $i <= 12; $i++): ?>
                            <option value="<?= $i ?>" <?= $i == $bulan ? 'selected' : '' ?>>
                                <?= date('F', mktime(0, 0, 0, $i, 1)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Tahun</label>
                    <select name="tahun" class="border border-gray-300 rounded px-3 py-2">
                        <?php for($i = date('Y'); $i >= date('Y')-5; $i--): ?>
                            <option value="<?= $i ?>" <?= $i == $tahun ? 'selected' : '' ?>><?= $i ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <div class="mt-5">
                    <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                        Filter
                    </button>
                </div>
            </form>
        </div>
        
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 p-6">
            <!-- Grafik Penjualan Harian -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">Penjualan Harian Bulan <?= date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) ?></h2>
                <canvas id="chartPenjualan" height="300"></canvas>
            </div>
            
            <!-- Produk Terlaris -->
            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-lg font-semibold mb-4">5 Produk Terlaris</h2>
                <div class="space-y-4">
                    <?php if(mysqli_num_rows($produk_terlaris) > 0): ?>
                        <?php while($produk = mysqli_fetch_assoc($produk_terlaris)): ?>
                        <div class="border rounded-lg p-3">
                            <div class="flex justify-between items-center">
                                <h3 class="font-medium"><?= htmlspecialchars($produk['nama_produk']) ?></h3>
                                <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded-full text-sm">
                                    <?= $produk['total_terjual'] ?> Terjual
                                </span>
                            </div>
                            <p class="text-sm text-gray-600 mt-1">
                                Total Penjualan: Rp <?= number_format($produk['total_penjualan'], 0, ',', '.') ?>
                            </p>
                        </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p class="text-gray-500">Tidak ada data penjualan produk</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Tabel Rincian Penjualan -->
        <div class="p-6">
            <h2 class="text-lg font-semibold mb-4">Rincian Penjualan</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Jumlah Transaksi</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Total Penjualan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php 
                        $total_all_transaksi = 0;
                        $total_all_penjualan = 0;
                        
                        for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun); $day++): 
                            $day_padded = str_pad($day, 2, '0', STR_PAD_LEFT);
                            $data = $data_penjualan[$day_padded] ?? null;
                            $total_all_transaksi += $data['jumlah_transaksi'] ?? 0;
                            $total_all_penjualan += $data['total_penjualan'] ?? 0;
                        ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $day_padded ?> <?= date('F Y', mktime(0, 0, 0, $bulan, 1, $tahun)) ?></td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $data['jumlah_transaksi'] ?? 0 ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($data['total_penjualan'] ?? 0, 0, ',', '.') ?></td>
                        </tr>
                        <?php endfor; ?>
                        <tr class="bg-gray-50 font-semibold">
                            <td class="px-6 py-4 whitespace-nowrap">Total</td>
                            <td class="px-6 py-4 whitespace-nowrap"><?= $total_all_transaksi ?></td>
                            <td class="px-6 py-4 whitespace-nowrap">Rp <?= number_format($total_all_penjualan, 0, ',', '.') ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Data untuk chart
    const labels = [];
    const dataTransaksi = [];
    const dataPenjualan = [];
    
    <?php 
    for($day = 1; $day <= cal_days_in_month(CAL_GREGORIAN, $bulan, $tahun); $day++) {
        $day_padded = str_pad($day, 2, '0', STR_PAD_LEFT);
        $data = $data_penjualan[$day_padded] ?? null;
        echo "labels.push('$day_padded');";
        echo "dataTransaksi.push(" . ($data['jumlah_transaksi'] ?? 0) . ");";
        echo "dataPenjualan.push(" . ($data['total_penjualan'] ?? 0) . ");";
    }
    ?>
    
    // Buat chart
    const ctx = document.getElementById('chartPenjualan').getContext('2d');
    const chart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Jumlah Transaksi',
                    data: dataTransaksi,
                    backgroundColor: 'rgba(59, 130, 246, 0.5)',
                    borderColor: 'rgba(59, 130, 246, 1)',
                    borderWidth: 1,
                    yAxisID: 'y'
                },
                {
                    label: 'Total Penjualan (Rp)',
                    data: dataPenjualan,
                    backgroundColor: 'rgba(16, 185, 129, 0.5)',
                    borderColor: 'rgba(16, 185, 129, 1)',
                    borderWidth: 1,
                    type: 'line',
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Jumlah Transaksi'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Total Penjualan (Rp)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                    ticks: {
                        callback: function(value) {
                            return 'Rp ' + value.toLocaleString();
                        }
                    }
                }
            },
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label.includes('Penjualan')) {
                                label += ': Rp ' + context.raw.toLocaleString();
                            } else {
                                label += ': ' + context.raw;
                            }
                            return label;
                        }
                    }
                }
            }
        }
    });
});
</script>

