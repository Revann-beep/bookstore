<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// SEARCH
$search = $_GET['search'] ?? '';

// AMBIL PRODUK MILIK SENDIRI
$produk = mysqli_query($conn, "
    SELECT p.*, k.nama_kategori
    FROM produk p
    LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
    WHERE p.id_penjual = '$id_penjual'
    AND p.nama_buku LIKE '%$search%'
    ORDER BY p.id_produk DESC
");

// HAPUS PRODUK (HANYA JIKA STOK = 0)
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];

    $cek = mysqli_query($conn, "
        SELECT stok FROM produk
        WHERE id_produk='$id'
        AND id_penjual='$id_penjual'
    ");
    $row = mysqli_fetch_assoc($cek);

    if ($row && $row['stok'] == 0) {
        mysqli_query($conn, "DELETE FROM produk WHERE id_produk='$id'");
        $_SESSION['success'] = "Produk berhasil dihapus!";
    } else {
        $_SESSION['error'] = "Produk tidak bisa dihapus karena stok masih ada!";
    }

    header("Location: produk.php");
    exit;
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Produk Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</head>
<body class="bg-slate-100 font-sans">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg flex flex-col h-screen">

  <!-- LOGO -->
  <div class="p-6 flex items-center gap-2 border-b">
    <div class="w-10 h-10 bg-teal-500 text-white rounded-full flex items-center justify-center font-bold">
      S
    </div>
    <span class="font-bold text-teal-600">SARI ANGREK</span>
  </div>

  <!-- MENU -->
  <div class="flex-1 px-4 py-6 space-y-2 text-sm">

    <!-- Dashboard -->
    <a href="dashboard.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      📊 Dashboard
    </a>

    <!-- Produk -->
    <a href="produk.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      📦 Produk
    </a>

    <!-- Approve -->
   <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleApprove()"
              class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-teal-100">
        <span class="flex items-center gap-3">
          ✅ Approve
        </span>
        <span id="iconApprove">▼</span>
      </button>

      <div id="approveMenu" class="hidden px-4 pb-2 space-y-2">
        <a href="approve.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Approve
        </a>
        <a href="laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Laporan
        </a>
        <a href="chat.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Chat
        </a>
        
      </div>
    </div>

    <!-- My Account -->
    <a href="akun_saya.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      👤 My Account
    </a>

    <!-- Sign Out -->
    <a href="../auth/logout.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-red-500">
      🔒 Sign Out
    </a>

  </div>

  <!-- HELP (paling bawah) -->
  <div class="px-4 py-4 border-t">
    <a href="help.php"
       class="flex items-center gap-3 text-gray-500 hover:text-teal-600">
      ❓ Help
    </a>
  </div>

</aside>


<!-- MAIN -->
<main class="flex-1 p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Produk Saya</h2>
        <a href="../auth/add_buku.php"
           class="bg-teal-500 hover:bg-teal-600 text-white px-5 py-2 rounded-xl font-semibold">
            + Tambah Produk
        </a>
    </div>

    <!-- SEARCH -->
    <form method="GET" class="mb-6">
        <input type="text" name="search"
               value="<?= htmlspecialchars($search) ?>"
               placeholder="Cari nama buku..."
               class="w-80 px-4 py-2 rounded-xl shadow outline-none">
    </form>

    <!-- LIST PRODUK -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">

    <?php if (mysqli_num_rows($produk) == 0): ?>
        <p class="text-gray-500">Produk belum ada.</p>
    <?php endif; ?>

    <?php while ($row = mysqli_fetch_assoc($produk)) :
        $margin = $row['harga'] - $row['modal'];
        $keuntungan = $margin * $row['stok'];
    ?>
        <div class="bg-white rounded-2xl shadow p-5 flex flex-col">

    <!-- GAMBAR PRODUK -->
    <div class="mb-4">
        <?php if (!empty($row['gambar']) && file_exists("../img/produk/" . $row['gambar'])): ?>
            <img src="../img/produk/<?= htmlspecialchars($row['gambar']) ?>"
                 alt="<?= htmlspecialchars($row['nama_buku']) ?>"
                 class="w-full h-48 object-cover rounded-xl">
        <?php else: ?>
            <div class="w-full h-48 bg-gray-200 rounded-xl flex items-center justify-center text-gray-500">
                Tidak ada gambar
            </div>
        <?php endif; ?>
    </div>


            <h3 class="font-bold text-lg mb-1"><?= htmlspecialchars($row['nama_buku']) ?></h3>
            <p class="text-sm text-gray-500 mb-2">
                Kategori: <?= $row['nama_kategori'] ?? '-' ?>
            </p>

            <div class="text-sm space-y-1 mb-4">
                <p>Stok: <strong><?= $row['stok'] ?></strong></p>
                <p>Harga: Rp<?= number_format($row['harga']) ?></p>
                <p>Modal: Rp<?= number_format($row['modal']) ?></p>
                <p>Margin: Rp<?= number_format($margin) ?></p>
                <p class="text-green-600 font-semibold">
                    Keuntungan: Rp<?= number_format($keuntungan) ?>
                </p>
            </div>

            <p class="text-sm text-gray-600 mb-4">
                <?= nl2br(htmlspecialchars($row['deskripsi'])) ?>
            </p>

            <!-- AKSI -->
            <div class="mt-auto flex gap-2">
                <a href="../auth/edit_buku.php?id=<?= $row['id_produk'] ?>"
                   class="flex-1 bg-blue-500 hover:bg-blue-600 text-white py-2 rounded text-center">
                   Edit
                </a>

                <?php if ($row['stok'] == 0): ?>
                    <a href="?hapus=<?= $row['id_produk'] ?>"
                       onclick="return confirm('Yakin hapus produk?')"
                       class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded text-center">
                       Hapus
                    </a>
                <?php else: ?>
                    <button 
    onclick="hapusProduk(<?= $row['id_produk'] ?>)"
    class="flex-1 bg-red-500 hover:bg-red-600 text-white py-2 rounded text-center">
    Hapus
</button>

                <?php endif; ?>
            </div>

        </div>
    <?php endwhile; ?>

    </div>

</main>
</div>
<?php if (isset($_SESSION['alert'])): ?>
<script>
Swal.fire({
    icon: '<?= $_SESSION['alert']['type'] ?>',
    title: '<?= $_SESSION['alert']['title'] ?>',
    text: '<?= $_SESSION['alert']['text'] ?>',
    confirmButtonColor: '#14b8a6'
});


function hapusProduk(id) {
    Swal.fire({
        title: 'Yakin hapus produk?',
        text: 'Data yang dihapus tidak bisa dikembalikan!',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        cancelButtonColor: '#9ca3af',
        confirmButtonText: 'Ya, hapus',
        cancelButtonText: 'Batal'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '?hapus=' + id;
        }
    });
}

<?php if (isset($_SESSION['success'])): ?>
<script>
Swal.fire({
    icon: 'success',
    title: 'Berhasil',
    text: '<?= $_SESSION['success'] ?>',
    confirmButtonColor: '#14b8a6'
});
</script>
<?php unset($_SESSION['success']); endif; ?>

<?php if (isset($_SESSION['error'])): ?>
<script>
Swal.fire({
    icon: 'error',
    title: 'Gagal',
    text: '<?= $_SESSION['error'] ?>',
    confirmButtonColor: '#ef4444'
});
</script>
<?php unset($_SESSION['error']); endif; ?>

</script>
<?php unset($_SESSION['alert']); endif; ?>

</body>
</html>
