<?php
session_start();
require '../auth/connection.php';

/* AMBIL DATA PRODUK + ID PENJUAL */
$produkQ = mysqli_query($conn, "
    SELECT 
        id_produk,
        id_penjual,
        nama_buku,
        harga,
        stok,
        gambar
    FROM produk
    ORDER BY created_at DESC
");

$id_user = $_SESSION['id_user'] ?? null;

/* AMBIL DATA USER */
$user = null;
if ($id_user) {
    $userQ = mysqli_query($conn, "
        SELECT nama, email, image
        FROM users
        WHERE id_user = '$id_user'
        LIMIT 1
    ");
    $user = mysqli_fetch_assoc($userQ);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Pesanan - Sari Anggrek</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg p-6">
  <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>
  <nav class="space-y-3">
    <a href="dashbord_pembeli.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Dashboard</a>
    <a href="halaman-pesanan.php" class="block px-4 py-3 rounded-xl bg-teal-500 text-white">Pesanan</a>
    <a href="halaman-keranjang.php" class="block px-10 text-gray-500">Keranjang</a>
    <a href="status.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Status</a>
    <a href="pesan.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Chat</a>
    <a href="my.php" class="block px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">My Account</a>
    <a href="../auth/logout.php" class="block px-4 py-3 rounded-xl text-red-500 hover:bg-red-50">Sign Out</a>
  </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">

<!-- TOPBAR -->
<div class="flex justify-between items-center mb-10">
  <h2 class="text-3xl font-bold">Pesanan</h2>
  <div class="flex items-center gap-3">
    <div class="text-right">
      <p class="font-semibold"><?= htmlspecialchars($user['nama'] ?? 'User'); ?></p>
      <p class="text-sm text-gray-500"><?= htmlspecialchars($user['email'] ?? ''); ?></p>
    </div>
    <img src="../img/profile/<?= $user['image'] ?? 'default.png'; ?>"
         class="w-10 h-10 rounded-full object-cover">
  </div>
</div>

<!-- PRODUK -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">

<?php while ($p = mysqli_fetch_assoc($produkQ)) : ?>
<div class="bg-white rounded-2xl shadow hover:shadow-xl transition overflow-hidden">

  <img src="../img/produk/<?= $p['gambar'] ?: 'default.png'; ?>"
       class="h-40 w-full object-cover">

  <div class="p-5 space-y-2">
    <h3 class="font-semibold text-lg"><?= htmlspecialchars($p['nama_buku']); ?></h3>

    <p class="text-sm text-gray-500">Stok: <?= $p['stok']; ?></p>

    <p class="text-teal-500 font-bold text-lg">
      Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
    </p>

<?php if ($p['stok'] > 0) : ?>
  <div class="flex gap-2 mt-3">

    <!-- PESAN -->
    <a href="../auth/add-keranjang.php?id=<?= $p['id_produk']; ?>"
       class="flex-1 text-center bg-teal-400 hover:bg-teal-500 text-white py-2 rounded-xl font-semibold">
      Pesan
    </a>

    <!-- CHAT PENJUAL -->
    <a href="pesan.php?id_penjual=<?= $p['id_penjual']; ?>&id_produk=<?= $p['id_produk']; ?>"
       class="w-12 flex items-center justify-center bg-blue-400 hover:bg-blue-500 text-white rounded-xl"
       title="Chat Penjual">
      💬
    </a>

  </div>
<?php else : ?>
  <button class="w-full mt-3 bg-gray-300 text-gray-600 py-2 rounded-xl cursor-not-allowed">
    Stok Habis
  </button>
<?php endif; ?>

  </div>
</div>
<?php endwhile; ?>

</div>

</main>
</div>
</body>
</html>
