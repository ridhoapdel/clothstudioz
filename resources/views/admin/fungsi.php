<?php
// Fungsi untuk tambah produk
function tambah_produk($nama, $kategori, $harga, $stok) {
    global $conn;
    $sql = "INSERT INTO produk (nama, kategori, harga, stok) VALUES ('$nama', '$kategori', '$harga', '$stok')";
    return $conn->query($sql);
}

// Fungsi untuk ambil data produk
function get_all_produk() {
    global $conn;
    $sql = "SELECT * FROM produk";
    return $conn->query($sql);
}

// Fungsi untuk edit produk
function edit_produk($id, $nama, $kategori, $harga, $stok) {
    global $conn;
    $sql = "UPDATE produk SET nama='$nama', kategori='$kategori', harga='$harga', stok='$stok' WHERE id='$id'";
    return $conn->query($sql);
}

// Fungsi untuk hapus produk
function hapus_produk($id) {
    global $conn;
    $sql = "DELETE FROM produk WHERE id='$id'";
    return $conn->query($sql);
}

// Fungsi untuk reset semua produk
function reset_semua_produk() {
    global $conn;
    $sql = "DELETE FROM produk";
    return $conn->query($sql);
}
?>
