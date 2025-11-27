<?php
session_start();
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit;
}
include '../dbconfig.php';



if (isset($_POST['submit'])) {
    // Validasi input
    $nama_produk = mysqli_real_escape_string($conn, $_POST['nama_produk'] ?? '');
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi'] ?? '');
    $harga = (float)($_POST['harga'] ?? 0);
    $kategori = (int)($_POST['kategori'] ?? 0);
    $sizes = $_POST['sizes'] ?? [];
    $stok_sizes = $_POST['stok_sizes'] ?? [];
    $diskon_persen = !empty($_POST['diskon_persen']) ? (float)$_POST['diskon_persen'] : 0;

    // Validasi
    if (empty($nama_produk) || $harga <= 0 || $kategori <= 0 || empty($sizes) || empty($stok_sizes)) {
        $error = "Semua kolom wajib diisi, termasuk setidaknya satu ukuran dan stok.";
    } else {
        // Validasi stok per ukuran
        $total_stok = 0;
        foreach ($sizes as $index => $size) {
            $stok = (int)($stok_sizes[$index] ?? 0);
            if ($stok < 0) {
                $error = "Stok tidak boleh negatif.";
                break;
            }
            $total_stok += $stok;
        }

        // Validasi gambar
        if (!isset($error) && isset($_FILES['gambar_produk']) && $_FILES['gambar_produk']['error'] === UPLOAD_ERR_OK) {
            $gambar = $_FILES['gambar_produk']['name'];
            $tmp = $_FILES['gambar_produk']['tmp_name'];
            $ext = strtolower(pathinfo($gambar, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'gif'];

            if (!in_array($ext, $allowed_ext)) {
                $error = "Format file tidak didukung. Gunakan JPG, JPEG, PNG, atau GIF.";
            } elseif ($_FILES['gambar_produk']['size'] > 20000000) { // 2MB max
                $error = "Ukuran file terlalu besar. Maksimal 2MB.";
            } else {
                $namaBaru = uniqid() . '.' . $ext;

                // Hitung harga diskon
                $harga_diskon = $harga;
                if ($diskon_persen > 0) {
                    $harga_diskon = $harga - ($harga * ($diskon_persen / 100));
                }

                // Mulai transaksi
                $conn->begin_transaction();
                try {
                    // Upload gambar
                    if (!move_uploaded_file($tmp, "../Uploads/" . $namaBaru)) {
                        throw new Exception("Gagal upload gambar.");
                    }

                    // Insert ke tabel produk
                    $stmt = $conn->prepare("
                        INSERT INTO produk (nama_produk, deskripsi, harga, kategori_id, gambar_produk, stok)
                        VALUES (?, ?, ?, ?, ?, ?)
                    ");
                    $stmt->bind_param("ssdisi", $nama_produk, $deskripsi, $harga, $kategori, $namaBaru, $total_stok);
                    $stmt->execute();
                    $produk_id = $conn->insert_id;
                    $stmt->close();

                    // Insert ke tabel produk_variasi
                    foreach ($sizes as $index => $size) {
                        $stok = (int)$stok_sizes[$index];
                        if ($stok > 0) { // Hanya simpan variasi dengan stok > 0
                            $stmt = $conn->prepare("
                                INSERT INTO produk_variasi (produk_id, ukuran, stok)
                                VALUES (?, ?, ?)
                            ");
                            $stmt->bind_param("isi", $produk_id, $size, $stok);
                            $stmt->execute();
                            $stmt->close();
                        }
                    }

                    // Insert ke tabel barang_diskon jika ada diskon
                    if ($diskon_persen > 0) {
                        $stmt = $conn->prepare("
                            INSERT INTO barang_diskon (produk_id, diskon_persen, mulai_diskon, selesai_diskon, harga_awal, harga_diskon)
                            VALUES (?, ?, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 7 DAY), ?, ?)
                        ");
                        $stmt->bind_param("iddd", $produk_id, $diskon_persen, $harga, $harga_diskon);
                        $stmt->execute();
                        $stmt->close();
                    }

                    $conn->commit();
                    header("Location: dashboard.php?success=1");
                    ob_end_flush();
                    exit;
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Gagal tambah produk: " . htmlspecialchars($e->getMessage());
                    error_log("tambah.php - Error: " . $e->getMessage());
                }
            }
        } else if (!isset($error)) {
            $error = "Gambar produk wajib diunggah.";
        }
    }
}
include '../layout/topbar.php';
include '../layout/header.php';
include '../layout/sidebar.php';

?>

<div id="content-wrapper" class="d-flex flex-column">
    <div id="content">
        <?php include '../layout/topbar.php'; ?>

        <div class="container-fluid">
            <?php if (isset($error)): ?>
                <div class="alert alert-danger">
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>

            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h1 class="h3 mb-0 text-gray-800">Tambah Produk</h1>
                </div>
                <div class="card-body">
                    <form method="post" enctype="multipart/form-data" id="formProduk">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Nama Produk</label>
                            <div class="col-sm-10">
                                <input type="text" name="nama_produk" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Deskripsi</label>
                            <div class="col-sm-10">
                                <textarea name="deskripsi" class="form-control" rows="3" required></textarea>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Harga Normal (Rp)</label>
                            <div class="col-sm-10">
                                <input type="number" name="harga" id="harga" class="form-control" min="0" step="100" required>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Diskon (%)</label>
                            <div class="col-sm-10">
                                <input type="number" name="diskon_persen" id="diskon_persen" class="form-control" min="0" max="100" step="1" value="0">
                                <small class="text-muted">Masukkan persentase diskon (0-100%)</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Harga Diskon</label>
                            <div class="col-sm-10">
                                <input type="text" id="harga_diskon" class="form-control-plaintext" readonly>
                                <small class="text-muted">Harga diskon akan berlaku selama 7 hari</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Kategori</label>
                            <div class="col-sm-10">
                                <select name="kategori" class="form-control" required>
                                    <?php
                                    $kategori = mysqli_query($conn, "SELECT * FROM kategori");
                                    while ($cat = mysqli_fetch_assoc($kategori)) {
                                        echo "<option value='{$cat['kategori_id']}'>{$cat['nama_kategori']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Ukuran dan Stok</label>
                            <div class="col-sm-10">
                                <div id="size-stok-container" class="space-y-2">
                                    <div class="size-stok-row flex items-center space-x-4">
                                        <select name="sizes[]" class="form-control w-1/3">
                                            <option value="M">M</option>
                                            <option value="L">L</option>
                                            <option value="XL">XL</option>
                                        </select>
                                        <input type="number" name="stok_sizes[]" min="0" placeholder="Stok"
                                               class="form-control w-1/3">
                                        <button type="button" class="remove-size btn btn-danger">-</button>
                                    </div>
                                </div>
                                <button type="button" id="add-size" class="btn btn-primary btn-sm mt-2">Tambah Ukuran</button>
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Gambar Produk</label>
                            <div class="col-sm-10">
                                <input type="file" name="gambar_produk" class="form-control-file" required accept="image/*">
                                <small class="text-muted">Format: JPG, PNG, GIF (Maks. 2MB)</small>
                            </div>
                        </div>
                        <div class="form-group row">
                            <div class="col-sm-10 offset-sm-2">
                                <button type="submit" name="submit" class="btn btn-primary">Tambah Produk</button>
                                <a href="dashboard.php" class="btn btn-secondary">Kembali</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Hitung harga diskon
        const hargaInput = document.getElementById('harga');
        const diskonInput = document.getElementById('diskon_persen');
        const hargaDiskonDisplay = document.getElementById('harga_diskon');

        function hitungHargaDiskon() {
            const harga = parseFloat(hargaInput.value) || 0;
            const diskon = parseFloat(diskonInput.value) || 0;
            if (diskon > 0) {
                const hargaDiskon = harga - (harga * (diskon / 100));
                hargaDiskonDisplay.value = 'Rp ' + hargaDiskon.toLocaleString('id-ID') + 
                                        ' (Diskon ' + diskon + '%)';
            } else {
                hargaDiskonDisplay.value = 'Rp ' + harga.toLocaleString('id-ID') + ' (Tidak ada diskon)';
            }
        }

        hargaInput.addEventListener('input', hitungHargaDiskon);
        diskonInput.addEventListener('input', hitungHargaDiskon);
        hitungHargaDiskon();

        // Tambah dan hapus ukuran
        document.getElementById('add-size').addEventListener('click', function() {
            const container = document.getElementById('size-stok-container');
            const newRow = document.createElement('div');
            newRow.className = 'size-stok-row flex items-center space-x-4 mb-2';
            newRow.innerHTML = `
                <select name="sizes[]" class="form-control w-1/3">
                    <option value="M">M</option>
                    <option value="L">L</option>
                    <option value="XL">XL</option>
                </select>
                <input type="number" name="stok_sizes[]" min="0" placeholder="Stok" 
                       class="form-control w-1/3">
                <button type="button" class="remove-size btn btn-danger">-</button>
            `;
            container.appendChild(newRow);
        });

        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('remove-size')) {
                e.target.parentElement.remove();
            }
        });
    });
    </script>

    <?php include '../layout/footer.php'; ?>
</div>