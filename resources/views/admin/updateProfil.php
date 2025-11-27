<?php
session_start();
include '../dbconfig.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $fullname = mysqli_real_escape_string($conn, $_POST['fullname']);
    $admin_id = $_SESSION['admin']['user_id'];

    $query = "UPDATE user SET email = '$email', fullname = '$fullname' WHERE user_id = $admin_id";
    
    if (mysqli_query($conn, $query)) {
        // Update session data
        $_SESSION['admin']['email'] = $email;
        $_SESSION['admin']['fullname'] = $fullname;
        
        $_SESSION['success'] = "Profil berhasil diperbarui!";
    } else {
        $_SESSION['error'] = "Gagal memperbarui profil: " . mysqli_error($conn);
    }
    
    header("Location: profile.php");
    exit();
}