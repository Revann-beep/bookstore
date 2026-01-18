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
  <title>Pesanan - Aksara Jiwa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    .brand-font {
      font-family: 'Playfair Display', serif;
    }
  </style>
</head>
<body class="bg-gradient-to-b from-slate-50 to-slate-100">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl p-6">
  <div class="mb-10">
    <h1 class="text-3xl font-bold text-amber-300 brand-font mb-1">AKSARA</h1>
    <h1 class="text-3xl font-bold text-amber-100 brand-font">JIWA</h1>
    <p class="text-slate-400 text-sm mt-2">Bookstore & Coffee</p>
  </div>
  <nav class="space-y-2">
    <a href="dashboard_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
      </svg>
      Dashboard
    </a>
    <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
      </svg>
      Pesanan
    </a>
    <a href="halaman-keranjang.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
      </svg>
      Keranjang
    </a>
    <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
      Status
    </a>
    <a href="pesan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
      </svg>
      Chat
    </a>
    <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
      </svg>
      My Account
    </a>
    <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-300 hover:bg-red-900/30 hover:text-red-200 transition-all duration-300 mt-8">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
      </svg>
      Sign Out
    </a>
  </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 p-8">

<!-- TOPBAR -->
<div class="flex justify-between items-center mb-10">
  <div>
    <h2 class="text-4xl font-bold text-slate-800 brand-font mb-2">Pesanan</h2>
    <p class="text-slate-600">Jelajahi koleksi buku terbaik kami</p>
  </div>
  <div class="flex items-center gap-3 bg-gradient-to-r from-slate-800 to-slate-900 px-5 py-3 rounded-xl shadow-lg">
    <div class="text-right">
      <p class="font-semibold text-white"><?= htmlspecialchars($user['nama'] ?? 'User'); ?></p>
      <p class="text-sm text-slate-300"><?= htmlspecialchars($user['email'] ?? ''); ?></p>
    </div>
    <div class="relative">
      <img src="../img/profile/<?= $user['image'] ?? 'default.png'; ?>"
           class="w-12 h-12 rounded-full object-cover border-2 border-amber-400">
      <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
    </div>
  </div>
</div>

<!-- PRODUK -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">

<?php while ($p = mysqli_fetch_assoc($produkQ)) : ?>
<div class="bg-white rounded-2xl shadow-lg hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 overflow-hidden border border-slate-100 group">

  <div class="relative overflow-hidden">
    <img src="../img/produk/<?= $p['gambar'] ?: 'default.png'; ?>"
         class="h-48 w-full object-cover group-hover:scale-105 transition-transform duration-500">
    <div class="absolute top-3 right-3 bg-amber-500 text-white text-xs font-bold px-3 py-1 rounded-full">
      BUKU
    </div>
    <?php if ($p['stok'] <= 0) : ?>
      <div class="absolute inset-0 bg-slate-900/70 flex items-center justify-center">
        <span class="text-white font-bold text-lg">STOK HABIS</span>
      </div>
    <?php endif; ?>
  </div>

  <div class="p-5 space-y-3">
    <h3 class="font-semibold text-lg text-slate-800 group-hover:text-amber-700 transition-colors duration-300">
      <?= htmlspecialchars($p['nama_buku']); ?>
    </h3>

    <div class="flex items-center justify-between">
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
        </svg>
        <p class="text-sm text-slate-600">Stok: <span class="font-semibold <?= $p['stok'] > 0 ? 'text-green-600' : 'text-red-600' ?>"><?= $p['stok']; ?></span></p>
      </div>
      <div class="flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
          <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
          <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
        </svg>
        <p class="text-amber-600 font-bold text-lg">
          Rp <?= number_format($p['harga'], 0, ',', '.'); ?>
        </p>
      </div>
    </div>

<?php if ($p['stok'] > 0) : ?>
  <div class="flex gap-2 mt-4">

    <!-- PESAN -->
    <a href="../auth/add-keranjang.php?id=<?= $p['id_produk']; ?>"
       class="flex-1 text-center bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white py-3 rounded-xl font-semibold transition-all duration-300 shadow hover:shadow-lg flex items-center justify-center gap-2">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
      </svg>
      Pesan
    </a>

    <!-- CHAT PENJUAL -->
    <a href="pesan.php?id_penjual=<?= $p['id_penjual']; ?>&id_produk=<?= $p['id_produk']; ?>"
       class="w-12 flex items-center justify-center bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-xl shadow hover:shadow-lg transition-all duration-300"
       title="Chat Penjual">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
      </svg>
    </a>

  </div>
<?php else : ?>
  <button class="w-full mt-4 bg-slate-300 text-slate-600 py-3 rounded-xl cursor-not-allowed font-semibold flex items-center justify-center gap-2">
    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
      <path fill-rule="evenodd" d="M13.477 14.89A6 6 0 015.11 6.524l8.367 8.368zm1.414-1.414L6.524 5.11a6 6 0 018.367 8.367zM18 10a8 8 0 11-16 0 8 8 0 0116 0z" clip-rule="evenodd" />
    </svg>
    Stok Habis
  </button>
<?php endif; ?>

  </div>
</div>
<?php endwhile; ?>

</div>

<!-- FOOTER -->
<footer class="text-center text-slate-500 mt-16 pb-4">
  <div class="flex items-center justify-center gap-2 mb-2">
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
  </div>
  <p>© <?= date('Y'); ?> <span class="text-amber-600 font-semibold brand-font">Aksara Jiwa</span> - Bookstore & Coffee</p>
  <p class="text-sm mt-1">Halaman Pesanan | <?= htmlspecialchars($user['nama'] ?? 'User'); ?></p>
</footer>

</main>
</div>
</body>
</html>