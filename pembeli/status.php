<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
  header("Location: ../auth/login.php");
  exit;
}

$id_user = $_SESSION['id_user'];

/* DATA USER LOGIN */
$userResult = mysqli_query($conn, "
  SELECT nama, image 
  FROM users 
  WHERE id_user = '$id_user'
");

// Cek apakah query user berhasil
if ($userResult && mysqli_num_rows($userResult) > 0) {
  $user = mysqli_fetch_assoc($userResult);
} else {
  $user = ['nama' => 'User', 'image' => 'default.png'];
}

/* DATA ORDER */
$q = mysqli_query($conn, "
  SELECT 
    o.id_order,
    o.status,
    o.bukti_tf,
    o.metode_pembayaran,
    o.no_resi,
    o.alamat_penjual,
    SUM(oi.qty) AS total_qty,
    GROUP_CONCAT(p.nama_buku SEPARATOR ', ') AS buku
  FROM orders o
  JOIN order_details oi ON o.id_order = oi.id_order
  JOIN produk p ON oi.id_produk = p.id_produk
  WHERE o.id_pembeli = '$id_user'
  GROUP BY o.id_order
  ORDER BY o.created_at DESC
");

// Cek apakah query order berhasil
if (!$q) {
  die("Query error: " . mysqli_error($conn));
}

$total_orders = mysqli_num_rows($q);
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Status Pesanan - Aksara Jiwa</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
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

<body class="bg-gradient-to-b from-slate-50 to-slate-100 min-h-screen flex">

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
    <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
      </svg>
      Pesanan
    </a>
    <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg">
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

<!-- CONTENT -->
<div class="flex-1 flex flex-col">

<!-- HEADER -->
<header class="bg-gradient-to-r from-slate-800 to-slate-900 shadow-lg px-8 py-5 flex justify-between items-center">
  <div class="flex items-center gap-3">
    <div class="p-3 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
      </svg>
    </div>
    <div>
      <h1 class="text-2xl font-bold text-white brand-font">Status Pesanan</h1>
      <p class="text-slate-300 text-sm">Lacak pesanan buku Anda</p>
    </div>
  </div>

  <div class="flex items-center gap-3 bg-gradient-to-r from-amber-900/30 to-amber-800/30 px-4 py-3 rounded-xl">
    <div class="text-right">
      <p class="font-semibold text-white"><?= htmlspecialchars($user['nama']) ?></p>
      <p class="text-sm text-amber-200">Member Aksara Jiwa</p>
    </div>
    <div class="relative">
      <img 
        src="../img/profile/<?= $user['image'] ?: 'default.png' ?>" 
        class="w-12 h-12 rounded-full object-cover border-2 border-amber-400"
      >
      <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
    </div>
  </div>
</header>

<!-- MAIN -->
<main class="p-8">

  <!-- STATISTIK -->
  <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
    <?php 
    mysqli_data_seek($q, 0);
    $total_orders = mysqli_num_rows($q);
    $pending = 0; $menunggu_verifikasi = 0; $paid = 0; $dikirim = 0; $refund = 0;
    while ($d = mysqli_fetch_assoc($q)) {
      if ($d['status'] == 'pending') $pending++;
      if ($d['status'] == 'menunggu_verifikasi') $pending++;
      if ($d['status'] == 'paid') $paid++;
      if ($d['status'] == 'dikirim') $dikirim++;
      if ($d['status'] == 'refund') $refund++;
    }
    mysqli_data_seek($q, 0);
    ?>
    <div class="bg-gradient-to-r from-amber-50 to-amber-100 rounded-2xl p-6 border border-amber-200">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-amber-800 font-medium">Total Pesanan</p>
          <p class="text-3xl font-bold text-amber-900"><?= $total_orders ?></p>
        </div>
        <div class="p-3 rounded-full bg-amber-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
            <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
            <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-r from-blue-50 to-blue-100 rounded-2xl p-6 border border-blue-200">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-blue-800 font-medium">Menunggu</p>
          <p class="text-3xl font-bold text-blue-900"><?= $pending ?></p>
        </div>
        <div class="p-3 rounded-full bg-blue-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-blue-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-r from-green-50 to-green-100 rounded-2xl p-6 border border-green-200">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-green-800 font-medium">Dibayar</p>
          <p class="text-3xl font-bold text-green-900"><?= $paid ?></p>
        </div>
        <div class="p-3 rounded-full bg-green-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-green-600" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
          </svg>
        </div>
      </div>
    </div>

    <div class="bg-gradient-to-r from-purple-50 to-purple-100 rounded-2xl p-6 border border-purple-200">
      <div class="flex justify-between items-center">
        <div>
          <p class="text-sm text-purple-800 font-medium">Dikirim</p>
          <p class="text-3xl font-bold text-purple-900"><?= $dikirim + $refund ?></p>
        </div>
        <div class="p-3 rounded-full bg-purple-500/20">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-purple-600" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4v1a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H20a1 1 0 001-1v-7a1 1 0 00-1-1h-8a1 1 0 00-1 1v2H9V5a1 1 0 00-1-1H3z" />
          </svg>
        </div>
      </div>
    </div>
  </div>

  <!-- TABLE -->
  <div class="bg-white rounded-3xl shadow-xl overflow-hidden">
    <div class="px-8 py-6 bg-gradient-to-r from-slate-50 to-slate-100 border-b">
      <div class="flex justify-between items-center">
        <h2 class="text-xl font-bold text-slate-800">Riwayat Pesanan</h2>
        <div class="text-sm text-slate-600">
          <?= $total_orders ?> pesanan ditemukan
        </div>
      </div>
    </div>

    <div class="overflow-x-auto">
      <table class="w-full">
        <thead class="bg-slate-50">
          <tr>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Kode Pesanan</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Judul Buku</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">QTY</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Bukti TF</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Pembayaran</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Status</th>
            <th class="py-4 px-6 text-left font-semibold text-slate-700">Aksi</th>
          </tr>
        </thead>

        <tbody class="divide-y divide-slate-100">
        <?php if ($total_orders == 0): ?>
          <tr>
            <td colspan="7" class="py-12 text-center">
              <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center rounded-full bg-gradient-to-br from-slate-50 to-slate-100">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-slate-400" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
              </div>
              <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum ada pesanan</h3>
              <p class="text-slate-500 mb-4">Mulailah petualangan membaca Anda dengan memesan buku pertama</p>
              <a href="halaman-pesanan.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-6 py-2 rounded-full font-semibold hover:shadow-lg transition-all duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                  <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                </svg>
                Jelajahi Buku
              </a>
            </td>
          </tr>
        <?php else: ?>
        <?php while ($d = mysqli_fetch_assoc($q)): ?>
          <tr class="hover:bg-slate-50 transition-colors duration-200">
            <td class="py-4 px-6">
              <div class="font-mono font-bold text-amber-600">#<?= $d['id_order'] ?></div>
              <div class="text-xs text-slate-500 mt-1"><?= $d['no_resi'] ?: 'Belum ada resi' ?></div>
            </td>
            
            <td class="py-4 px-6">
              <div class="font-medium text-slate-800"><?= htmlspecialchars(mb_strimwidth($d['buku'], 0, 50, '...')) ?></div>
            </td>
            
            <td class="py-4 px-6">
              <div class="inline-flex items-center gap-2 bg-slate-100 text-slate-700 px-3 py-1 rounded-full text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                  <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                  <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
                <?= $d['total_qty'] ?> item
              </div>
            </td>
            
            <td class="py-4 px-6">
              <?php if ($d['bukti_tf']): ?>
                <div class="group relative cursor-pointer">
                  <img src="../img/bukti/<?= $d['bukti_tf'] ?>" class="w-16 h-16 object-cover rounded-lg shadow border border-slate-200">
                  <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity duration-300 rounded-lg flex items-center justify-center">
                    <span class="text-white text-xs font-medium">Lihat</span>
                  </div>
                </div>
              <?php else: ?>
                <div class="text-slate-400 text-sm">Belum upload</div>
              <?php endif; ?>
            </td>
            
            <td class="py-4 px-6">
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm <?= $d['metode_pembayaran'] == 'Transfer Bank' ? 'bg-blue-100 text-blue-700' : 'bg-purple-100 text-purple-700' ?>">
                <?= $d['metode_pembayaran'] ?>
              </div>
            </td>
            
            <td class="py-4 px-6">
              <?php 
              $statusColors = [
                'pending' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'menunggu_verifikasi' => 'bg-yellow-100 text-yellow-800 border-yellow-200',
                'paid' => 'bg-green-100 text-green-800 border-green-200',
                'dikirim' => 'bg-blue-100 text-blue-800 border-blue-200',
                'refund' => 'bg-purple-100 text-purple-800 border-purple-200'
              ];
              ?>
              <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-sm font-medium border <?= $statusColors[$d['status']] ?>">
                <?php if ($d['status'] == 'pending'): ?>
                  <div class="w-2 h-2 rounded-full bg-yellow-500 animate-pulse"></div>
                <?php elseif ($d['status'] == 'menunggu_verifikasi'): ?>
                  <div class="w-2 h-2 rounded-full bg-yellow-500"></div>
                <?php elseif ($d['status'] == 'paid'): ?>
                  <div class="w-2 h-2 rounded-full bg-green-500"></div>
                <?php elseif ($d['status'] == 'dikirim'): ?>
                  <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <?php elseif ($d['status'] == 'refund'): ?>
                  <div class="w-2 h-2 rounded-full bg-blue-500"></div>
                <?php else: ?>
                  <div class="w-2 h-2 rounded-full bg-purple-500"></div>
                <?php endif; ?>
                <?= ucfirst($d['status']) ?>
              </div>
              <div class="text-xs text-slate-500 mt-1">
                <?= $d['status'] == 'paid' ? 'Disetujui' : 'Menunggu' ?>
              </div>
            </td>
            
            <!-- ACTION -->
            <td class="py-4 px-6">
              <div class="flex gap-2">
                <!-- Lihat Invoice -->
                <a href="invoice.php?id_order=<?= $d['id_order'] ?>"
                   class="flex items-center gap-1 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg text-sm font-medium transition-all duration-300 shadow hover:shadow-lg">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-11a1 1 0 10-2 0v3.586L7.707 9.293a1 1 0 00-1.414 1.414l3 3a1 1 0 001.414 0l3-3a1 1 0 00-1.414-1.414L11 10.586V7z" clip-rule="evenodd" />
                  </svg>
                  Invoice
                </a>

                <!-- Upload Bukti -->
                <?php if ($d['status']=='pending' && !$d['bukti_tf']): ?>
                  <a href="../auth/upload-bukti.php?id_order=<?= $d['id_order'] ?>"
                     class="flex items-center gap-1 px-4 py-2 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white rounded-lg text-sm font-medium transition-all duration-300 shadow hover:shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                      <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                    </svg>
                    Upload
                  </a>
                <?php endif; ?>
              </div>
            </td>
          </tr>
        <?php endwhile; ?>
        <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>

  <!-- FOOTER -->
  <footer class="text-center text-slate-500 mt-12 pb-4">
    <div class="flex items-center justify-center gap-2 mb-2">
      <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
      <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
      <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
    </div>
    <p>© <?= date('Y'); ?> <span class="text-amber-600 font-semibold brand-font">Aksara Jiwa</span> - Bookstore & Coffee</p>
    <p class="text-sm mt-1">Status Pesanan | <?= htmlspecialchars($user['nama']) ?></p>
  </footer>

</main>

</div>
</body>
</html>