<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// AMBIL KATEGORI
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY nama_kategori ASC");

// SIMPAN PRODUK
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nama        = mysqli_real_escape_string($conn, $_POST['nama_buku']);
    $id_kategori = $_POST['id_kategori'];
    $stok        = (int)$_POST['stok'];
    $harga       = (int)$_POST['harga'];
    $modal       = (int)$_POST['modal'];
    $deskripsi   = mysqli_real_escape_string($conn, $_POST['deskripsi']);

    // ================== CEK DUPLIKAT NAMA ==================
    $cek = mysqli_query($conn, "
        SELECT * FROM produk 
        WHERE id_penjual='$id_penjual' AND nama_buku='$nama'
    ");
    if (mysqli_num_rows($cek) > 0) {
        $_SESSION['error'] = "Anda sudah memiliki produk dengan nama yang sama!";
        header("Location: ../penjual/produk.php");
        exit;
    }

    // ================== UPLOAD GAMBAR ==================
    $gambar = null;

    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {

        $allowed = ['jpg','jpeg','png','webp','jfif'];
        $ext = strtolower(pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION));

        if (!in_array($ext, $allowed)) {
            $_SESSION['error'] = "Format gambar tidak diizinkan";
            header("Location: ../penjual/produk.php");
            exit;
        }

        // pastikan folder ada
        $folder = "../img/produk/";
        if (!is_dir($folder)) mkdir($folder, 0777, true);

        $gambar = uniqid('buku_') . '.' . $ext;
        $target = $folder . $gambar;

        if (!move_uploaded_file($_FILES['gambar']['tmp_name'], $target)) {
            $_SESSION['error'] = "Upload gambar gagal";
            header("Location: ../penjual/produk.php");
            exit;
        }
    }

    // ================== INSERT DB ==================
    $query = mysqli_query($conn, "
        INSERT INTO produk 
        (id_penjual, id_kategori, nama_buku, stok, harga, modal, deskripsi, gambar)
        VALUES
        ('$id_penjual', '$id_kategori', '$nama', '$stok', '$harga', '$modal', '$deskripsi', '$gambar')
    ");

    if ($query) {
        $_SESSION['success'] = "Produk berhasil ditambahkan!";
    } else {
        $_SESSION['error'] = "Produk gagal ditambahkan!";
    }

    header("Location: ../penjual/produk.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Produk</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>


    <!-- HITUNG MARGIN -->
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
        <a href="dashboard.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">
            Dashboard
        </a>
        <a href="../penjual/produk.php" class="flex px-4 py-3 rounded-xl bg-teal-100 text-teal-600 font-semibold">
            Produk
        </a>
        <a href="akun_saya.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">
            My Account
        </a>
        <a href="../auth/logout.php" class="flex px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50">
            Sign Out
        </a>
    </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">

    <h2 class="text-2xl font-bold mb-6 text-gray-800">Tambah Produk Buku</h2>

    <form method="POST" enctype="multipart/form-data"
          class="bg-white p-6 rounded-2xl shadow max-w-xl space-y-4">

        <input type="text" name="nama_buku" placeholder="Nama Buku"
               class="w-full border rounded-lg px-4 py-3" required>

        <select name="id_kategori" class="w-full border rounded-lg px-4 py-3" required>
            <option value="">-- Pilih Kategori Buku --</option>
            <?php while ($k = mysqli_fetch_assoc($kategori)) : ?>
                <option value="<?= $k['id_kategori'] ?>">
                    <?= htmlspecialchars($k['nama_kategori']) ?>
                </option>
            <?php endwhile; ?>
        </select>

        <input type="number" name="stok" placeholder="Stok"
               class="w-full border rounded-lg px-4 py-3" required>

        <input type="number" id="harga" name="harga" placeholder="Harga Jual"
               oninput="hitungMargin()"
               class="w-full border rounded-lg px-4 py-3" required>

        <input type="number" id="modal" name="modal" placeholder="Modal"
               oninput="hitungMargin()"
               class="w-full border rounded-lg px-4 py-3" required>

        <input type="text" id="margin" placeholder="Margin"
               class="w-full border rounded-lg px-4 py-3 bg-gray-100" readonly>

        <!-- GAMBAR PRODUK -->
        <div>
            <label class="block mb-1 text-gray-600">Gambar Buku</label>
            <input type="file" name="gambar" accept="image/*"
                   class="w-full border rounded-lg px-4 py-2">
        </div>

        <textarea name="deskripsi" rows="4" placeholder="Deskripsi Buku"
                  class="w-full border rounded-lg px-4 py-3"></textarea>

        <div class="flex gap-3">
            <button class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-lg">
                Simpan
            </button>
            <a href="../penjual/produk.php"
               class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg">
               Batal
            </a>
        </div>

    </form>

</main>
</div>

<?php if (isset($_SESSION['success'])) : ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= $_SESSION['success'] ?>',
    confirmButtonColor: '#14b8a6'
}).then(() => {
    window.location.href = '../penjual/produk.php';
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])) : ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= $_SESSION['error'] ?>',
    confirmButtonColor: '#ef4444'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

</body>
</html>
