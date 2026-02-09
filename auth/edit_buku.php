<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];
$id_produk  = $_GET['id'] ?? null;

if (!$id_produk) {
    header("Location: ../penjual/produk.php");
    exit;
}

// AMBIL DATA PRODUK (HARUS MILIK PENJUAL)
$produk = mysqli_query($conn, "
    SELECT * FROM produk 
    WHERE id_produk='$id_produk' AND id_penjual='$id_penjual'
");

$data = mysqli_fetch_assoc($produk);
if (!$data) {
    header("Location: ../penjual/produk.php");
    exit;
}

// AMBIL KATEGORI
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// UPDATE PRODUK
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama        = mysqli_real_escape_string($conn, $_POST['nama_buku']);
    $id_kategori = $_POST['id_kategori'];
    $stok        = $_POST['stok'];
    $harga       = $_POST['harga'];
    $modal       = $_POST['modal'];
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // ===== CEK DUPLIKAT NAMA PRODUK UNTUK PENJUAL INI =====
    $cek = mysqli_query($conn, "
        SELECT * FROM produk 
        WHERE id_penjual='$id_penjual' AND nama_buku='$nama' AND id_produk!='$id_produk'
    ");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Gagal',
            'text' => 'Anda sudah memiliki produk dengan nama yang sama!'
        ];
        header("Location: ../penjual/produk.php");
        exit;
    }

    $gambar_lama = $data['gambar'];
    $gambar_baru = $gambar_lama;

    // JIKA UPLOAD GAMBAR BARU
    if (!empty($_FILES['gambar']['name'])) {
        $ext_valid = ['jpg','jpeg','png','webp','jfif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (in_array($ext, $ext_valid)) {
            $gambar_baru = uniqid('buku_') . '.' . $ext;
            move_uploaded_file(
                $_FILES['gambar']['tmp_name'],
                "../img/produk/" . $gambar_baru
            );

            // HAPUS GAMBAR LAMA
            if ($gambar_lama && file_exists("../img/produk/$gambar_lama")) {
                unlink("../img/produk/$gambar_lama");
            }
        }
    }

    // UPDATE DATABASE
    $update = mysqli_query($conn, "
        UPDATE produk SET
            nama_buku='$nama',
            id_kategori='$id_kategori',
            stok='$stok',
            harga='$harga',
            modal='$modal',
            deskripsi='$deskripsi',
            gambar='$gambar_baru'
        WHERE id_produk='$id_produk' AND id_penjual='$id_penjual'
    ");

    if ($update) {
        $_SESSION['alert'] = [
            'type' => 'success',
            'title' => 'Berhasil',
            'text' => 'Produk berhasil diperbarui'
        ];
    } else {
        $_SESSION['alert'] = [
            'type' => 'error',
            'title' => 'Gagal',
            'text' => 'Produk gagal diperbarui'
        ];
    }

    header("Location: ../penjual/produk.php");
    exit;

}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Edit Produk</title>
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


<script>
function hitungMargin() {
    const harga = document.getElementById('harga').value || 0;
    const modal = document.getElementById('modal').value || 0;
    document.getElementById('margin').value = harga - modal;
}
</script>
</head>

<body class="bg-slate-100">
<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg p-6">
    <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>
    <nav class="space-y-3">
        <a href="dashboard.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Dashboard</a>
        <a href="produk.php" class="flex px-4 py-3 rounded-xl bg-teal-100 text-teal-600 font-semibold">Produk</a>
        <a href="akun_saya.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">My Account</a>
        <a href="../auth/logout.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50">Sign Out</a>
    </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">
<h2 class="text-2xl font-bold mb-6">Edit Produk Buku</h2>

<form method="POST" enctype="multipart/form-data"
      class="bg-white p-6 rounded-2xl shadow max-w-xl space-y-4">

    <input type="text" name="nama_buku"
           value="<?= htmlspecialchars($data['nama_buku']) ?>"
           class="w-full border rounded-lg px-4 py-3" required>

    <select name="id_kategori" class="w-full border rounded-lg px-4 py-3" required>
        <?php while ($k = mysqli_fetch_assoc($kategori)) : ?>
            <option value="<?= $k['id_kategori'] ?>"
                <?= $data['id_kategori']==$k['id_kategori']?'selected':'' ?>>
                <?= htmlspecialchars($k['nama_kategori']) ?>
            </option>
        <?php endwhile; ?>
    </select>

    <input type="number" name="stok" value="<?= $data['stok'] ?>"
           class="w-full border rounded-lg px-4 py-3" required>

    <input type="number" id="harga" name="harga"
           value="<?= $data['harga'] ?>"
           oninput="hitungMargin()"
           class="w-full border rounded-lg px-4 py-3" required>

    <input type="number" id="modal" name="modal"
           value="<?= $data['modal'] ?>"
           oninput="hitungMargin()"
           class="w-full border rounded-lg px-4 py-3" required>

    <input type="text" id="margin"
           value="<?= $data['harga'] - $data['modal'] ?>"
           class="w-full border rounded-lg px-4 py-3 bg-gray-100" readonly>

    <!-- GAMBAR -->
    <div>
        <p class="text-sm mb-1">Gambar Saat Ini</p>
        <img src="../img/produk/<?= $data['gambar'] ?: 'default.png' ?>"
             class="w-24 h-32 object-cover rounded mb-2">
        <input type="file" name="gambar" accept="image/*"
               class="w-full border rounded-lg px-4 py-2">
        <p class="text-xs text-gray-500 mt-1">Kosongkan jika tidak ingin mengganti</p>
    </div>

    <textarea name="deskripsi" rows="4"
              class="w-full border rounded-lg px-4 py-3"><?= htmlspecialchars($data['deskripsi']) ?></textarea>

    <div class="flex gap-3">
        <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-lg">
            Update
        </button>
        <a href="../penjual/produk.php"
           class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg">
           Batal
        </a>
    </div>

</form>
</main>
</div>
</body>
</html>
