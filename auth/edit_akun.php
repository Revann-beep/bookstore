<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// ambil data user
$data = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($data);

$error = '';
$success = '';

// CEK BATAS 7 HARI GANTI IMAGE
$bolehGantiimage = true;
if (!empty($user['last_photo_update'])) {
    $last = strtotime($user['last_photo_update']);
    $now  = time();
    if (($now - $last) < (7 * 24 * 60 * 60)) {
        $bolehGantiimage = false;
    }
}

// PROSES UPDATE
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $imageBaru = $user['image']; // default pakai yang lama

    // UPLOAD IMAGE (JIKA ADA)
    if (!empty($_FILES['image']['name'])) {

        if (!$bolehGantiimage) {
            $error = "Image profil hanya bisa diganti 7 hari sekali.";
        } else {

            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Format gambar harus JPG / PNG / WEBP.";
            } else {

                $folder = "../img/profile/";
                if (!is_dir($folder)) {
                    mkdir($folder, 0777, true);
                }

                $namaFile = 'profile_' . $id_user . '_' . time() . '.' . $ext;
                $target = $folder . $namaFile;

                // UPLOAD
                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $error = "Upload gambar gagal. Cek permission folder.";
                } else {

                    // HAPUS FOTO LAMA (jika ada)
                    if (!empty($user['image']) && file_exists($user['image'])) {
                        unlink($user['image']);
                    }

                    // update nama file ke DB
                    $imageBaru = $target;

                    // update last_photo_update
                    mysqli_query($conn, "UPDATE users SET last_photo_update=NOW() WHERE id_user='$id_user'");
                }
            }
        }
    }

    // UPDATE DATA
    if (!$error) {
        mysqli_query($conn, "UPDATE users SET 
            nama='$nama',
            email='$email',
            image='$imageBaru'
            WHERE id_user='$id_user'
        ");

        $success = "Data berhasil diperbarui";
    }
}
?>



<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Akun</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-xl shadow w-full max-w-md">

    <h2 class="text-xl font-bold mb-6 text-center">Edit Akun Saya</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-600 p-3 rounded mb-4"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- image -->
        <div class="text-center">
            <img src="<?= $user['image'] ?: '../assets/default_user.png' ?>"
                 class="w-24 h-24 rounded-full mx-auto object-cover mb-2">

            <?php if ($bolehGantiimage): ?>
                <input type="file" name="image" class="text-sm mx-auto">
                <p class="text-xs text-gray-500 mt-1">Boleh ganti image (7 hari sekali)</p>
            <?php else: ?>
                <p class="text-xs text-red-500">image bisa diganti setelah 7 hari</p>
            <?php endif; ?>
        </div>

        <!-- NAMA -->
        <div>
            <label class="text-sm font-medium">Nama</label>
            <input type="text" name="nama" value="<?= htmlspecialchars($user['nama']) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-300" required>
        </div>

        <!-- EMAIL -->
        <div>
            <label class="text-sm font-medium">Email</label>
            <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>"
                   class="w-full px-4 py-2 border rounded-lg focus:ring focus:ring-teal-300" required>
        </div>

        <!-- BUTTON -->
        <button type="submit"
                class="w-full bg-teal-500 hover:bg-teal-600 text-white py-2 rounded-lg">
            Simpan Perubahan
        </button>

        <a href="../penjual/admin.php"
           class="block text-center text-sm text-gray-500 hover:underline">
           Kembali
        </a>
    </form>

</div>

</body>
</html>
