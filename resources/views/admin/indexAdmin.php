<?php
include '../dbconfig.php';
include 'fungsi.php';

// Proses pencarian dan filter
$search = isset($_GET['search']) ? $_GET['search'] : '';
$kategori_filter = isset($_GET['kategori']) ? $_GET['kategori'] : '';

// Query untuk menampilkan produk dengan pencarian dan filter
$sql = "SELECT * FROM produk WHERE nama_produk LIKE ? AND kategori_id LIKE ?";
$stmt = mysqli_prepare($conn, $sql);  // Ubah dari $conn->prepare() ke mysqli_prepare()

// Bind parameter
$search_term = "%$search%";
$kategori_term = "%$kategori_filter%";
mysqli_stmt_bind_param($stmt, "ss", $search_term, $kategori_term);

// Eksekusi query
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Menghitung total produk dan stok
$total_produk = 0;
$total_stok = 0;
while ($row = mysqli_fetch_assoc($result)) {
    $total_produk++;
    $total_stok += $row['stok'];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">

</head>
<body id="page-top">
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion">
            <li class="nav-item">
                <a class="nav-link" href="index.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span></a>
            </li>
        </ul>

        <div id="content-wrapper" class="d-flex flex-column">
            <div id="content">
                <div class="container-fluid">
                    <h1 class="h3 mb-2 text-gray-800">Daftar Produk</h1>
                    <div class="row">
                        <div class="col-12">
                            <form method="GET" action="">
                                <input type="text" name="search" value="<?php echo $search; ?>" placeholder="Cari produk..." class="form-control">
                                <select name="kategori" class="form-control">
                                    <option value="">Semua Kategori</option>
                                    <option value="Elektronik" <?php echo ($kategori_filter == 'Elektronik') ? 'selected' : ''; ?>>Elektronik</option>
                                    <option value="Fashion" <?php echo ($kategori_filter == 'Fashion') ? 'selected' : ''; ?>>Fashion</option>
                                    <option value="Rumah Tangga" <?php echo ($kategori_filter == 'Rumah Tangga') ? 'selected' : ''; ?>>Rumah Tangga</option>
                                </select>
                                <button type="submit" class="btn btn-primary">Cari</button>
                            </form>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <h3>Total Produk: <?php echo $total_produk; ?> | Total Stok: <?php echo $total_stok; ?></h3>
                            <a href="tambah.php" class="btn btn-success mb-3">Tambah Produk</a>
                            <a href="reset.php" class="btn btn-danger mb-3">Reset Semua Produk</a>

                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Nama Produk</th>
                                        <th>Kategori</th>
                                        <th>Harga</th>
                                        <th>Stok</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $no = 1;
                                    while ($row = mysqli_fetch_assoc($result)) {
                                    ?>
                                    <tr>
                                        <td><?php echo $no++; ?></td>
                                        <td><?php echo $row['nama']; ?></td>
                                        <td><?php echo $row['kategori']; ?></td>
                                        <td><?php echo $row['harga']; ?></td>
                                        <td><?php echo $row['stok']; ?></td>
                                        <td>
                                            <a href="edit.php?id=<?php echo $row['id']; ?>" class="btn btn-warning">Edit</a>
                                            <a href="hapus.php?id=<?php echo $row['id']; ?>" class="btn btn-danger">Hapus</a>
                                        </td>
                                    </tr>
                                    <?php
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.4/jquery.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.6.2/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"></script>
</body>
</html>
