<?php
session_start();
include '../dbconfig.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $query = "SELECT * FROM user WHERE username='$username' AND role='admin'";
    $result = mysqli_query($conn, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['admin'] = $user;
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Username atau password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Admin - Cloth Studio</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex items-center justify-center h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg w-full max-w-md">
        <div class="text-center mb-6">
            <img src="../aset/png logo.png" alt="Cloth Studio Logo" class="h-16 mx-auto">
            <h2 class="text-2xl font-bold mt-4">Login Admin</h2>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-100 text-red-700 p-3 rounded mb-4 text-sm">
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4">
            <div>
                <label class="block text-sm font-medium">Username</label>
                <input type="text" name="username" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring focus:border-blue-500">
            </div>
            <div>
                <label class="block text-sm font-medium">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-2 border rounded focus:outline-none focus:ring focus:border-blue-500">
            </div>
            <button type="submit" class="w-full bg-black text-white py-2 rounded hover:bg-gray-800 transition">Login</button>
        </form>
    </div>
</body>
</html>