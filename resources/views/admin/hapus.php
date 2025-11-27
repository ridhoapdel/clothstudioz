<?php
include '../dbconfig.php';

if (isset($_GET['id'])) {
    $produk_id = (int)$_GET['id'];
    
    // Mulai transaksi
    mysqli_begin_transaction($conn);
    
    try {
        // 1. Hapus diskon terkait
        mysqli_query($conn, "DELETE FROM barang_diskon WHERE produk_id = $produk_id");
        
      
        // 4. Hapus produk
        mysqli_query($conn, "DELETE FROM produk WHERE produk_id = $produk_id");
        
        // Commit transaksi jika semua query berhasil
        mysqli_commit($conn);
        
        header("Location: dashboard.php?success=1");
    } catch (Exception $e) {
        // Rollback jika ada error
        mysqli_rollback($conn);
        header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
    }
} else {
    header("Location: dashboard.php");
}
?>