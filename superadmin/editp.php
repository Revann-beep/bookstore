<?php
session_start();
require '../auth/connection.php';

/* ===================== CEK ROLE ===================== */
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit;
}

/* ===================== TENTUKAN ID PEMBELI ===================== */
$id_user = $_GET['id'] ?? null;
if (!$id_user) {
    header("Location: pembeli.php");
    exit;
}

/* ===================== AMBIL DATA PEMBELI ===================== */
$data = mysqli_query($conn, "
    SELECT * FROM users 
    WHERE id_user='$id_user' AND role='pembeli'
");
if (mysqli_num_rows($data) === 0) {
    header("Location: pembeli.php");
    exit;
}
$user = mysqli_fetch_assoc($data);

$error = '';
$success = '';

/* ===================== CEK BATAS 7 HARI GANTI FOTO ===================== */
$bolehGantiimage = true;
if (!empty($user['last_photo_update'])) {
    $last = strtotime($user['last_photo_update']);
    $now  = time();
    if (($now - $last) < (7 * 24 * 60 * 60)) {
        $bolehGantiimage = false;
    }
}

/* ===================== PROSES UPDATE ===================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama  = htmlspecialchars($_POST['nama']);
    $email = htmlspecialchars($_POST['email']);
    $imageBaru = $user['image']; // default pakai yang lama

    // ===== UPLOAD IMAGE =====
    if (!empty($_FILES['image']['name'])) {
        if (!$bolehGantiimage) {
            $error = "Image profil hanya bisa diganti 7 hari sekali.";
        } else {
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg','jpeg','png','webp'];

            if (!in_array($ext, $allowed)) {
                $error = "Format gambar harus JPG / PNG / WEBP.";
            } else {
                $folder = "../img/profile/";
                if (!is_dir($folder)) mkdir($folder, 0777, true);

                $namaFile = 'pembeli_' . $id_user . '_' . time() . '.' . $ext;
                $target = $folder . $namaFile;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
                    $error = "Upload gambar gagal. Cek permission folder.";
                } else {
                    if (!empty($user['image']) && file_exists($user['image'])) {
                        unlink($user['image']);
                    }
                    $imageBaru = $target;

                    mysqli_query($conn, "
                        UPDATE users 
                        SET last_photo_update=NOW() 
                        WHERE id_user='$id_user'
                    ");
                }
            }
        }
    }

    // ===== UPDATE DATA =====
    if (!$error) {
        // CEK EMAIL UNIK
        $cek_email = mysqli_query($conn, "SELECT * FROM users WHERE email='$email' AND id_user!='$id_user'");
        if (mysqli_num_rows($cek_email) > 0) {
            $error = "Email sudah terdaftar!";
        } else {
            mysqli_query($conn, "
                UPDATE users SET 
                    nama='$nama',
                    email='$email',
                    image='$imageBaru'
                WHERE id_user='$id_user'
            ");
            $success = "Data pembeli berhasil diperbarui";
            // refresh data
            $user = mysqli_fetch_assoc(mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'"));
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Pembeli</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center">

<div class="bg-white p-8 rounded-xl shadow w-full max-w-md">

    <h2 class="text-xl font-bold mb-6 text-center">Edit Pembeli</h2>

    <?php if ($error): ?>
        <div class="bg-red-100 text-red-600 p-3 rounded mb-4"><?= $error ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="bg-green-100 text-green-600 p-3 rounded mb-4"><?= $success ?></div>
    <?php endif; ?>

    <form method="POST" enctype="multipart/form-data" class="space-y-4">

        <!-- IMAGE -->
        <div class="text-center">
            <img src="<?= $user['image'] ?: '../assets/default_user.png' ?>"
                 class="w-24 h-24 rounded-full mx-auto object-cover mb-2">

            <?php if ($bolehGantiimage): ?>
                <input type="file" name="image" class="text-sm mx-auto">
                <p class="text-xs text-gray-500 mt-1">Boleh ganti image (7 hari sekali)</p>
            <?php else: ?>
                <p class="text-xs text-red-500">Image bisa diganti setelah 7 hari</p>
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

        <a href="pembeli.php"
           class="block text-center text-sm text-gray-500 hover:underline">
           Kembali
        </a>
    </form>

</div>

</body>
</html>