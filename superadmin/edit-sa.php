<?php
session_start();
require '../auth/connection.php';

/* =====================
   CEK LOGIN & ROLE
===================== */
if (
    !isset($_SESSION['id_user']) ||
    !isset($_SESSION['role']) ||
    $_SESSION['role'] !== 'super_admin'
) {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* =====================
   AMBIL DATA USER
===================== */
$q = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($q);

if (!$user) {
    die("Data user tidak ditemukan");
}

$error = '';
$success = '';

/* =====================
   CEK BATAS 7 HARI FOTO
===================== */
$bolehGantiImage = true;
if (!empty($user['last_photo_update'])) {
    $last = strtotime($user['last_photo_update']);
    if ((time() - $last) < (7 * 24 * 60 * 60)) {
        $bolehGantiImage = false;
    }
}

/* =====================
   PROSES UPDATE
===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $imageBaru = $user['image'];

    /* ==== UPLOAD FOTO ==== */
    if (!empty($_FILES['image']['name'])) {

        if (!$bolehGantiImage) {
            $error = "Foto profil hanya bisa diganti 7 hari sekali.";
        } else {

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Format gambar harus JPG, PNG, atau WEBP.";
            } else {

                $folder = "../img/profile/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $namaFile = 'sa_' . $id_user . '_' . time() . '.' . $ext;
                $target   = $folder . $namaFile;

                if (move_uploaded_file($_FILES['image']['tmp_name'], $target)) {

                    // hapus foto lama
                    if (!empty($user['image']) && file_exists($user['image'])) {
                        unlink($user['image']);
                    }

                    $imageBaru = $target;

                    mysqli_query($conn, "
                        UPDATE users 
                        SET last_photo_update = NOW() 
                        WHERE id_user='$id_user'
                    ");
                } else {
                    $error = "Gagal upload foto.";
                }
            }
        }
    }

    /* ==== UPDATE DB ==== */
    if (!$error) {
        mysqli_query($conn, "
            UPDATE users SET
                nama  = '$nama',
                email = '$email',
                image = '$imageBaru'
            WHERE id_user = '$id_user'
        ");

        $success = "Akun Super Admin berhasil diperbarui.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Akun Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-xl shadow w-full max-w-md">

    <h2 class="text-xl font-bold mb-6 text-center text-purple-600">
        Edit Akun Super Admin
    </h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4">
            <?= $error ?>
        </div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-600 p-3 rounded mb-4">
            <?= $success ?>
        </div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- FOTO -->
        <div class="text-center">
            <img src="<?= $user['image'] ?: '../assets/default_user.png' ?>"
                 class="w-24 h-24 rounded-full mx-auto object-cover mb-2">

            <?php if ($bolehGantiImage): ?>
                <input type="file" name="image" class="text-sm mx-auto">
                <p class="text-xs text-gray-500 mt-1">
                    Foto bisa diganti 7 hari sekali
                </p>
            <?php else: ?>
                <p class="text-xs text-red-500">
                    Foto bisa diganti setelah 7 hari
                </p>
            <?php endif; ?>
        </div>

        <!-- NAMA -->
        <div>
            <label class="text-sm font-medium">Nama</label>
            <input type="text" name="nama"
                   value="<?= htmlspecialchars($user['nama']) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-purple-300"
                   required>
        </div>

        <!-- EMAIL -->
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email" name="email"
                   value="<?= htmlspecialchars($user['email']) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-purple-300"
                   required>
        </div>

        <button type="submit"
                class="w-full bg-purple-600 hover:bg-purple-700 text-white py-2 rounded-lg">
            Simpan Perubahan
        </button>

        <a href="dashboard.php"
           class="block text-center text-sm text-gray-500 hover:underline">
            Kembali
        </a>
    </form>

</div>

</body>
</html>