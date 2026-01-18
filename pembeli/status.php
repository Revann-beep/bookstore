<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
  header("Location: ../auth/login.php");
  exit;
}

$id_user = $_SESSION['id_user'];

/* DATA USER LOGIN */
$user = mysqli_fetch_assoc(mysqli_query($conn, "
  SELECT nama, image 
  FROM users 
  WHERE id_user = '$id_user'
"));

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
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Status Pesanan</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100 min-h-screen flex">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg p-6">
  <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>

  <nav class="space-y-3">
    <a href="dashboard_pembeli.php" class="block px-4 py-3 rounded-xl hover:bg-teal-50">Dashboard</a>
    <a href="halaman-pesanan.php" class="block px-4 py-3 rounded-xl hover:bg-teal-50">Pesanan</a>
    <a href="status.php" class="block px-4 py-3 rounded-xl bg-teal-500 text-white">Status</a>
    <a href="pesan.php" class="block px-4 py-3 rounded-xl hover:bg-teal-50">Chat</a>
    <a href="my.php" class="block px-4 py-3 rounded-xl hover:bg-teal-50">My Account</a>
    <a href="../auth/logout.php" class="block px-4 py-3 rounded-xl text-red-500 hover:bg-red-50">Sign Out</a>
  </nav>
</aside>

<!-- CONTENT -->
<div class="flex-1 flex flex-col">

<!-- HEADER -->
<header class="bg-white shadow px-8 py-4 flex justify-between items-center">
  <div class="font-bold text-teal-600 text-lg">Status Pesanan</div>

  <div class="flex items-center gap-3">
    <div class="text-right">
      <p class="font-semibold"><?= htmlspecialchars($user['nama']) ?></p>
      <!-- <p class="text-sm text-gray-500">@<?= htmlspecialchars($user['username']) ?></p> -->
    </div>

    <img 
      src="../img/profile/<?= $user['image'] ?: 'default.png' ?>" 
      class="w-10 h-10 rounded-full object-cover"
    >
  </div>
</header>

<!-- MAIN -->
<main class="p-8">

  <div class="overflow-x-auto bg-white shadow rounded-xl p-6">
    <table class="w-full border text-sm text-center">
      <thead class="bg-gray-100">
        <tr class="font-semibold">
          <th class="border">Kode</th>
          <th class="border">Judul Buku</th>
          <th class="border">QTY</th>
          <th class="border">Bukti</th>
          <th class="border">Pembayaran</th>
          <th class="border">Approve</th>
          <th class="border">Status</th>
          <th class="border">Resi</th>
          <th class="border">Alamat</th>
        </tr>
      </thead>

      <tbody>
      <?php while ($d = mysqli_fetch_assoc($q)): ?>
        <tr>
          <td class="border py-2"><?= $d['id_order'] ?></td>
          <td class="border"><?= htmlspecialchars($d['buku']) ?></td>
          <td class="border"><?= $d['total_qty'] ?></td>

          <td class="border">
            <?php if ($d['bukti_tf']): ?>
              <img src="../img/bukti/<?= $d['bukti_tf'] ?>" class="w-10 mx-auto">
            <?php else: ?>
              -
            <?php endif; ?>
          </td>

          <td class="border"><?= $d['metode_pembayaran'] ?></td>

          <td class="border font-semibold <?= $d['status']=='paid'?'text-green-600':'text-yellow-600' ?>">
            <?= $d['status']=='paid'?'Disetujui':'Menunggu' ?>
          </td>

          <td class="border"><?= ucfirst($d['status']) ?></td>
          <td class="border"><?= $d['no_resi'] ?: '-' ?></td>
          <td class="border"><?= $d['alamat_penjual'] ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>
  </div>

</main>

</div>
</body>
</html>
