<?php
session_start();
include '../dbconfig.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['profile_photo'])) {
    $admin_id = $_SESSION['admin']['user_id'];
    $target_dir = "../uploads/profiles/";
    
    // Create directory if not exists
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_name = basename($_FILES["profile_photo"]["name"]);
    $target_file = $target_dir . time() . '_' . preg_replace("/[^a-zA-Z0-9.]/", "_", $file_name);
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Check if image file is a actual image
    $check = getimagesize($_FILES["profile_photo"]["tmp_name"]);
    if ($check === false) {
        $_SESSION['error'] = "File bukan gambar.";
        header("Location: profile.php");
        exit();
    }

    // Check file size (5MB max)
    if ($_FILES["profile_photo"]["size"] > 5000000) {
        $_SESSION['error'] = "Maaf, file terlalu besar. Maksimal 5MB.";
        header("Location: profile.php");
        exit();
    }

    // Allow certain file formats
    if (!in_array($imageFileType, ['jpg', 'jpeg', 'png'])) {
        $_SESSION['error'] = "Maaf, hanya file JPG, JPEG, PNG yang diperbolehkan.";
        header("Location: profile.php");
        exit();
    }

    // Try to upload file
    if (move_uploaded_file($_FILES["profile_photo"]["tmp_name"], $target_file)) {
        // Update database
        $relative_path = "profiles/" . basename($target_file);
        $query = "UPDATE user SET profile_photo = '$relative_path' WHERE user_id = $admin_id";
        
        if (mysqli_query($conn, $query)) {
            // Update session
            $_SESSION['admin']['profile_photo'] = $relative_path;
            $_SESSION['success'] = "Foto profil berhasil diunggah!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan ke database: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error'] = "Maaf, terjadi kesalahan saat mengunggah file.";
    }
    
    header("Location: profile.php");
    exit();
}