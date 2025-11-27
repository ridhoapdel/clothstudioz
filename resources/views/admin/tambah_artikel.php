<?php
session_start();
include '../dbconfig.php';

include '../layout/header.php';
include '../layout/sidebar.php';
include '../layout/topbar.php';

// Cek admin login
if (!isset($_SESSION['admin'])) {
    header("Location: loginAdmin.php");
    exit();
}

$errors = [];
$success = false;
$judul = $konten = $penulis = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil dan sanitize input
    $judul = trim($_POST['judul'] ?? '');
    $konten = trim($_POST['konten'] ?? '');
    $penulis = trim($_POST['penulis'] ?? '');

    // Validasi input
    if (empty($judul)) $errors[] = "Judul is required!";
    if (empty($konten)) $errors[] = "Konten is required!";
    if (empty($penulis)) $errors[] = "Penulis is required!";

    // Handle file upload
    $gambar = "";
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png'];
        $file_ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));
        if (!in_array($file_ext, $allowed)) {
            $errors[] = "Only JPG, JPEG, PNG files are allowed!";
        } elseif ($_FILES['gambar']['size'] > 5 * 1024 * 1024) {
            $errors[] = "File size must be under 5MB!";
        } else {
            $gambar = time() . "_" . $_FILES['gambar']['name'];
            $upload_dir = "../uploads/";
            if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $upload_dir . $gambar)) {
                $errors[] = "Failed to upload image!";
            }
        }
    } else {
        $errors[] = "Image is required!";
    }

    // Insert ke database kalo ga ada error
    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO artikel (judul, konten, gambar, penulis) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssss", $judul, $konten, $gambar, $penulis);
        if ($stmt->execute()) {
            $success = true;
            header("Location: dashboard.php?success=artikel_added");
            exit();
        } else {
            $errors[] = "Error saving artikel: " . $conn->error;
        }
        $stmt->close();
    }
}
?>

<div id="content-wrapper" class="flex flex-col min-h-screen">
    <div id="content">
        <?php include '../layout/topbar.php'; ?>
        
        <div class="container mx-auto px-4 py-6">
            <div class="bg-white shadow-md rounded-lg">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h1 class="text-2xl font-bold text-gray-800">Tambah Artikel Inspirasi</h1>
                </div>
                <div class="p-6">
                    <?php if ($success): ?>
                        <div class="bg-green-100 text-green-700 p-4 rounded mb-4">
                            Artikel berhasil ditambahkan!
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($errors)): ?>
                        <div class="bg-red-100 text-red-700 p-4 rounded mb-4">
                            <?php foreach ($errors as $error): ?>
                                <p><?php echo htmlspecialchars($error); ?></p>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                    <form method="POST" enctype="multipart/form-data" class="space-y-6">
                        <div>
                            <label for="judul" class="block text-sm font-medium text-gray-700">Judul</label>
                            <input type="text" id="judul" name="judul" value="<?php echo htmlspecialchars($judul); ?>" class="mt-1 w-full px-4 py-2 border rounded-md focus:ring focus:ring-blue-300" required>
                        </div>
                        <div>
                            <label for="konten" class="block text-sm font-medium text-gray-700">Konten</label>
                            <textarea id="konten" name="konten" class="mt-1 w-full px-4 py-2 border rounded-md focus:ring focus:ring-blue-300" rows="6" required><?php echo htmlspecialchars($konten); ?></textarea>
                        </div>
                        <div>
                            <label for="gambar" class="block text-sm font-medium text-gray-700">Gambar</label>
                            <input type="file" id="gambar" name="gambar" accept="image/*" class="mt-1 w-full px-4 py-2 border rounded-md" required>
                            <p class="mt-1 text-sm text-gray-500">Format: JPG, PNG (Maks. 5MB)</p>
                        </div>
                        <div>
                            <label for="penulis" class="block text-sm font-medium text-gray-700">Penulis</label>
                            <input type="text" id="penulis" name="penulis" value="<?php echo htmlspecialchars($penulis); ?>" class="mt-1 w-full px-4 py-2 border rounded-md focus:ring focus:ring-blue-300" required>
                        </div>
                        <div class="flex space-x-4">
                            <button type="submit" class="bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Tambah Artikel</button>
                            <a href="dashboard.php" class="bg-gray-300 text-gray-800 py-2 px-4 rounded-md hover:bg-gray-400">Kembali</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    
    <?php include '../layout/footer.php'; ?>
</div>