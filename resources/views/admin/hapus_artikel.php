<?php
session_start();
include '../dbconfig.php';

// Cek admin login
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit();
}

if (isset($_GET['id'])) {
    $artikel_id = (int)$_GET['id'];
    
    // Ambil data artikel untuk hapus gambar
    $stmt = $conn->prepare("SELECT gambar FROM artikel WHERE artikel_id = ?");
    $stmt->bind_param("i", $artikel_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $artikel = $result->fetch_assoc();
    $stmt->close();

    if ($artikel) {
        // Mulai transaksi
        mysqli_begin_transaction($conn);
        
        try {
            // Hapus artikel
            $stmt = $conn->prepare("DELETE FROM artikel WHERE artikel_id = ?");
            $stmt->bind_param("i", $artikel_id);
            $stmt->execute();
            $stmt->close();

            // Hapus gambar dari Uploads/
            if ($artikel['gambar'] && file_exists("../Uploads/" . $artikel['gambar'])) {
                unlink("../Uploads/" . $artikel['gambar']);
            }

            // Commit transaksi
            mysqli_commit($conn);
            
            header("Location: dashboard.php?success=artikel_deleted");
            exit();
        } catch (Exception $e) {
            // Rollback jika error
            mysqli_rollback($conn);
            header("Location: dashboard.php?error=" . urlencode($e->getMessage()));
            exit();
        }
    }
}

header("Location: dashboard.php");
exit();
?>