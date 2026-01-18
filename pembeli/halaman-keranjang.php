<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

/* USER */
$userQ = mysqli_query($conn, "
    SELECT nama, image 
    FROM users 
    WHERE id_user = '$id_user'
");
$user = mysqli_fetch_assoc($userQ);

/* KERANJANG */
$cartQ = mysqli_query($conn, "
    SELECT 
        k.id_keranjang,
        k.qty,
        p.nama_buku,
        p.harga,
        p.gambar
    FROM keranjang k
    JOIN produk p ON k.id_produk = p.id_produk
    WHERE k.id_user = '$id_user'
");

$total = 0;
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Keranjang</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100">

<div class="flex min-h-screen">

<!-- SIDEBAR -->
<aside class="w-64 bg-white shadow-lg p-6">
    <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>
    <nav class="space-y-3">
      <a href="Dashbord_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Dashboard</a>
      <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-teal-400 to-teal-500 text-white">Pesanan</a>
      <a href="halaman-keranjang.php" class="pl-10 text-gray-500 block ">Keranjang</a>
      <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Status</a>
      <a href="pesan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Chat</a>
      <a href="report.html" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Laporan</a>
      <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">My Account</a>
      <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50">Sign Out</a>
    </nav>
  </aside>

<!-- MAIN -->
<main class="flex-1 p-8">

<!-- TOPBAR -->
<div class="flex justify-end mb-8">
  <div class="flex items-center gap-3">
    <div class="text-right">
      <p class="font-semibold"><?= htmlspecialchars($user['nama']) ?></p>
    </div>
    <img src="../img/profile/<?= $user['image'] ?: 'default.png' ?>" class="w-10 h-10 rounded-full">
  </div>
</div>

<h2 class="text-3xl font-bold text-center mb-10">Keranjang</h2>

<div class="max-w-4xl mx-auto space-y-6">

<?php if (mysqli_num_rows($cartQ) == 0): ?>
  <p class="text-center text-gray-500">Keranjang masih kosong</p>
<?php endif; ?>

<?php while ($c = mysqli_fetch_assoc($cartQ)) :
  $subtotal = $c['qty'] * $c['harga'];
  $total += $subtotal;
?>

<!-- ITEM -->
<div class="bg-white rounded-2xl shadow p-6 flex justify-between items-center">
  <div class="flex items-center gap-4">
    <img src="../img/produk/<?= $c['gambar'] ?: 'default.png' ?>" class="w-16 h-16 object-cover">
    <div>
      <p class="font-semibold"><?= htmlspecialchars($c['nama_buku']) ?></p>
      <p class="text-sm text-gray-500">Rp <?= number_format($c['harga'],0,',','.') ?></p>
    </div>
  </div>

  <div class="flex items-center gap-4">
    <form action="../auth/addqty.php" method="post">
      <input type="hidden" name="id_keranjang" value="<?= $c['id_keranjang'] ?>">
      <input type="number" name="qty" value="<?= $c['qty'] ?>" min="1"
             class="w-16 text-center border rounded-lg"
             onchange="this.form.submit()">
    </form>

    <p class="font-semibold text-teal-500">
      Rp <?= number_format($subtotal,0,',','.') ?>
    </p>

    <a href="../auth/delete_keranjang.php?id=<?= $c['id_keranjang'] ?>"
       class="text-red-500 hover:text-red-700">🗑️</a>
  </div>
</div>

<?php endwhile; ?>

<!-- TOTAL -->
<?php if ($total > 0): ?>
<div class="bg-white rounded-2xl shadow p-6 flex justify-between items-center">
  <p class="text-lg font-semibold">Total Harga</p>
  <p class="text-xl font-bold text-teal-500">
    Rp <?= number_format($total,0,',','.') ?>
  </p>
</div>

<div class="text-center">
  <a href="../auth/checkout.php"
     class="bg-gradient-to-r from-teal-400 to-teal-500 text-white px-16 py-4 rounded-full text-lg font-semibold">
    Lanjut
  </a>
</div>
<?php endif; ?>

</div>

</main>
</div>

</body>
</html>
