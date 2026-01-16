<?php
session_start();
require 'auth/connection.php';

$error = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $username = $_POST['username'];
    $password = $_POST['password'];

    $query = mysqli_query($conn, "
        SELECT * FROM users 
        WHERE (nik='$username' OR email='$username')
        LIMIT 1
    ");

    $user = mysqli_fetch_assoc($query);

    if ($user && password_verify($password, $user['password'])) {

        // Set session
        $_SESSION['id_user'] = $user['id_user'];
        $_SESSION['nama']    = $user['nama'];
        $_SESSION['role']    = $user['role'];

        // Update last_activity
        mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='{$user['id_user']}'");

        // Redirect sesuai role
        if ($user['role'] === 'super_admin') {
            header("Location: superadmin/dashboard.php");
        } elseif ($user['role'] === 'penjual') {
            header("Location: penjual/dashboard.php");
        } elseif ($user['role'] === 'pembeli') {
            header("Location: pembeli/dashboard_pembeli.php");
        } else {
            $error = "Role tidak dikenali";
        }
        exit;

    } else {
        $error = "NIK / Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100 flex items-center justify-center">

<div class="bg-white rounded-2xl shadow-2xl overflow-hidden max-w-4xl w-full grid grid-cols-1 md:grid-cols-2">

    <!-- KIRI : GAMBAR / LOGO -->
    <div class="hidden md:flex items-center justify-center bg-gradient-to-br from-teal-500 to-emerald-600 p-10">
        <div class="text-center text-white">
            <img src="assets/logo.png" alt="Logo" class="w-40 mx-auto mb-6">
            <h2 class="text-2xl font-bold">Sistem Informasi</h2>
            <p class="text-sm opacity-90 mt-2">
                Login untuk mengakses dashboard
            </p>
        </div>
    </div>

    <!-- KANAN : FORM LOGIN -->
    <div class="p-10">
        <h2 class="text-3xl font-bold text-gray-800 mb-2">Login</h2>
        <p class="text-gray-500 mb-6">Masuk ke akun Anda</p>

        <?php if ($error): ?>
            <div class="bg-red-100 text-red-600 text-sm p-3 rounded-lg mb-4">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="POST" class="space-y-4">

            <input type="text" name="username" placeholder="NIK / Email"
                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none"
                required>

            <input type="password" name="password" placeholder="Password"
                class="w-full px-4 py-3 border rounded-xl focus:ring-2 focus:ring-teal-500 focus:outline-none"
                required>

            <button type="submit" name="login"
                class="w-full bg-teal-500 hover:bg-teal-600 text-white py-3 rounded-xl font-semibold transition">
                Login
            </button>

            <div class="flex justify-between text-sm">
                <a href="auth/forgot_password.php" class="text-teal-600 hover:underline">
                    Lupa Password?
                </a>
                <a href="auth/register.php" class="text-teal-600 font-semibold hover:underline">
                    Daftar
                </a>
            </div>

        </form>
    </div>

</div>

</body>
</html>
