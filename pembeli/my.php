<?php
session_start();
include '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$page = $_GET['page'] ?? 'profile';

$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$data = mysqli_fetch_assoc($query);

/* PROSES UPDATE */
if (isset($_POST['update'])) {

    $nama   = $_POST['nama'];
    $email  = $_POST['email'];
    $alamat = $_POST['alamat'];

    if (!empty($_FILES['image']['name'])) {
        $folder = "../img/profile/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $nama_image = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], $folder . $nama_image);

        if ($data['image'] && $data['image'] != 'default.png') {
            @unlink($folder . $data['image']);
        }

        $updateimage = ", image='$nama_image'";
    } else {
        $updateimage = "";
    }

    mysqli_query($conn, "
        UPDATE users SET
        nama='$nama',
        email='$email',
        alamat='$alamat'
        $updateimage
        WHERE id_user='$id_user'
    ");

    echo "<script>alert('Data berhasil diperbarui');location='my.php';</script>";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>My Account</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-100">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg p-6">
  <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>
  <nav class="space-y-3">
    <a href="dashboard_pembeli.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Dashboard</a>
    <a href="halaman-pesanan.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Pesanan</a>
    <a href="status.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Status</a>
    <a href="pesan.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Chat</a>
    <a href="report.html" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Laporan</a>
    <a href="my.php" class="block px-4 py-3 rounded-xl bg-gradient-to-r from-teal-400 to-teal-500 text-white">My Account</a>
    <a href="../auth/logout.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50">Sign Out</a>
  </nav>
</aside>

<!-- MAIN CONTENT -->
<main class="flex-1 p-10">

<div class="max-w-xl mx-auto bg-white p-6 rounded-2xl shadow">

<h2 class="text-2xl font-bold mb-6 text-center">My Account</h2>

<?php if ($page == 'profile') { ?>

    <div class="text-center">
        <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
             class="w-28 h-28 rounded-full mx-auto mb-4 object-cover border">

        <p class="font-bold text-lg"><?= $data['nama']; ?></p>
        <p class="text-gray-500"><?= $data['email']; ?></p>
    </div>

    <div class="flex justify-center gap-3 mt-6">
        <a href="?page=detail" class="bg-blue-500 text-white px-5 py-2 rounded-lg">Detail</a>
        <a href="?page=edit" class="bg-yellow-500 text-white px-5 py-2 rounded-lg">Edit</a>
    </div>

<?php } elseif ($page == 'detail') { ?>

    <div class="text-center mb-4">
        <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
             class="w-24 h-24 rounded-full mx-auto object-cover border">
    </div>

    <table class="w-full text-sm">
        <tr><td class="py-1">Nama</td><td>: <?= $data['nama']; ?></td></tr>
        <tr><td class="py-1">Email</td><td>: <?= $data['email']; ?></td></tr>
        <tr><td class="py-1">Alamat</td><td>: <?= $data['alamat']; ?></td></tr>
        <tr><td class="py-1">Terdaftar</td><td>: <?= $data['created_at']; ?></td></tr>
    </table>

    <a href="my.php" class="inline-block mt-5 bg-gray-500 text-white px-5 py-2 rounded-lg">
        Kembali
    </a>

<?php } elseif ($page == 'edit') { ?>

<form method="POST" enctype="multipart/form-data">

    <div class="text-center mb-4">
        <img src="../img/profile/<?= $data['image'] ?? 'default.png'; ?>"
             class="w-24 h-24 rounded-full mx-auto object-cover border">
    </div>

    <label class="text-sm font-semibold">Foto Profil</label>
    <input type="file" name="image" class="w-full mb-3">

    <input type="text" name="nama" value="<?= $data['nama']; ?>"
        class="w-full mb-3 p-2 border rounded" required>

    <input type="email" name="email" value="<?= $data['email']; ?>"
        class="w-full mb-3 p-2 border rounded" required>

    <textarea name="alamat" class="w-full mb-3 p-2 border rounded"><?= $data['alamat']; ?></textarea>

    <div class="flex gap-3">
        <button name="update" class="bg-blue-500 text-white px-5 py-2 rounded-lg">
            Simpan
        </button>
        <a href="my.php" class="bg-gray-500 text-white px-5 py-2 rounded-lg">
            Batal
        </a>
    </div>

</form>

<?php } ?>

</div>
</main>
</div>

</body>
</html>
