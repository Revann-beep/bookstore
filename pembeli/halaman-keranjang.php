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
  <title>Keranjang - Aksara Jiwa</title>
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
      <a href="Dashbord_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
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
      <a href="halaman-keranjang.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg">
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
      <a href="report.html" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        Laporan
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
<div class="flex justify-end mb-10">
  <div class="flex items-center gap-3 bg-gradient-to-r from-slate-800 to-slate-900 px-5 py-3 rounded-xl shadow-lg">
    <div class="text-right">
      <p class="font-semibold text-white"><?= htmlspecialchars($user['nama']) ?></p>
      <p class="text-sm text-slate-300">Keranjang Anda</p>
    </div>
    <div class="relative">
      <img src="../img/profile/<?= $user['image'] ?: 'default.png' ?>" 
           class="w-12 h-12 rounded-full object-cover border-2 border-amber-400">
      <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
    </div>
  </div>
</div>

<!-- HEADER -->
<div class="text-center mb-12">
  <h2 class="text-4xl font-bold text-slate-800 brand-font mb-3">Keranjang Belanja</h2>
  <p class="text-slate-600 max-w-2xl mx-auto">Kumpulkan buku favorit Anda dan nikmati pengalaman membaca yang tak terlupakan bersama Aksara Jiwa</p>
</div>

<div class="max-w-5xl mx-auto">

<?php if (mysqli_num_rows($cartQ) == 0): ?>
  <!-- KERANJANG KOSONG -->
  <div class="bg-white rounded-3xl shadow-xl p-12 text-center border border-slate-100">
    <div class="w-32 h-32 mx-auto mb-6 flex items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-amber-100">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-16 w-16 text-amber-400" viewBox="0 0 20 20" fill="currentColor">
        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3z" />
      </svg>
    </div>
    <h3 class="text-2xl font-bold text-slate-700 mb-3">Keranjang Masih Kosong</h3>
    <p class="text-slate-500 mb-8">Mulailah petualangan membaca Anda dengan menjelajahi koleksi buku kami</p>
    <a href="halaman-pesanan.php" class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all duration-300">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
      </svg>
      Jelajahi Buku
    </a>
  </div>
<?php endif; ?>

<?php while ($c = mysqli_fetch_assoc($cartQ)) :
  $subtotal = $c['qty'] * $c['harga'];
  $total += $subtotal;
?>

<!-- ITEM -->
<div class="bg-white rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 p-6 mb-4 flex justify-between items-center border border-slate-100 group">
  <div class="flex items-center gap-6">
    <div class="relative">
      <img src="../img/produk/<?= $c['gambar'] ?: 'default.png' ?>" 
           class="w-24 h-24 object-cover rounded-xl shadow">
      <div class="absolute -top-2 -right-2 w-8 h-8 bg-amber-500 text-white text-xs font-bold rounded-full flex items-center justify-center">
        <?= $c['qty'] ?>
      </div>
    </div>
    <div>
      <h3 class="font-bold text-lg text-slate-800 group-hover:text-amber-700 transition-colors duration-300">
        <?= htmlspecialchars($c['nama_buku']) ?>
      </h3>
      <div class="flex items-center gap-4 mt-2">
        <div class="flex items-center gap-2 text-slate-600">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
          </svg>
          <span class="font-semibold text-amber-600">Rp <?= number_format($c['harga'],0,',','.') ?></span>
        </div>
        <div class="flex items-center gap-2 text-slate-500 text-sm">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
          </svg>
          Update: <?= date('H:i') ?>
        </div>
      </div>
    </div>
  </div>

  <div class="flex items-center gap-8">
    <form action="../auth/addqty.php" method="post" class="flex items-center gap-4">
      <input type="hidden" name="id_keranjang" value="<?= $c['id_keranjang'] ?>">
      <div class="flex items-center border border-slate-200 rounded-xl overflow-hidden">
        <button type="button" onclick="decrementQty(this)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600">
          -
        </button>
        <input type="number" name="qty" value="<?= $c['qty'] ?>" min="1"
               class="w-16 text-center border-0 focus:ring-0 focus:outline-none"
               onchange="this.form.submit()">
        <button type="button" onclick="incrementQty(this)" class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-600">
          +
        </button>
      </div>
      <button type="submit" class="text-sm text-amber-600 hover:text-amber-800 font-medium">
        Update
      </button>
    </form>

    <div class="text-right min-w-32">
      <p class="text-sm text-slate-500">Subtotal</p>
      <p class="text-xl font-bold text-amber-600">
        Rp <?= number_format($subtotal,0,',','.') ?>
      </p>
    </div>

    <a href="../auth/delete_keranjang.php?id=<?= $c['id_keranjang'] ?>"
       class="p-3 rounded-xl bg-red-50 hover:bg-red-100 text-red-500 hover:text-red-700 transition-all duration-300"
       title="Hapus dari keranjang">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M9 2a1 1 0 00-.894.553L7.382 4H4a1 1 0 000 2v10a2 2 0 002 2h8a2 2 0 002-2V6a1 1 0 100-2h-3.382l-.724-1.447A1 1 0 0011 2H9zM7 8a1 1 0 012 0v6a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v6a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd" />
      </svg>
    </a>
  </div>
</div>

<?php endwhile; ?>

<!-- TOTAL & CHECKOUT -->
<?php if ($total > 0): ?>
<div class="mt-12 space-y-6">
  <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-3xl shadow-xl p-8">
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-bold text-white">Ringkasan Pembelian</h3>
      <div class="text-amber-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
        </svg>
      </div>
    </div>
    <div class="flex justify-between items-center">
      <div>
        <p class="text-slate-300">Total Harga</p>
        <p class="text-sm text-slate-400"><?= mysqli_num_rows($cartQ) ?> item dalam keranjang</p>
      </div>
      <div class="text-right">
        <p class="text-3xl font-bold text-white">
          Rp <?= number_format($total,0,',','.') ?>
        </p>
        <p class="text-sm text-slate-300 mt-1">Termasuk pajak</p>
      </div>
    </div>
  </div>

  <div class="flex justify-between items-center pt-6">
    <a href="halaman-pesanan.php" class="flex items-center gap-2 text-slate-600 hover:text-slate-800 font-medium">
      <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
      </svg>
      Lanjutkan Belanja
    </a>
    <a href="../auth/checkout.php"
       class="flex items-center gap-3 bg-gradient-to-r from-amber-500 to-amber-600 hover:from-amber-600 hover:to-amber-700 text-white px-10 py-4 rounded-full text-lg font-semibold shadow-lg hover:shadow-xl transition-all duration-300">
       Lanjut ke Checkout
       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
         <path fill-rule="evenodd" d="M10.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L12.586 11H5a1 1 0 110-2h7.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
       </svg>
    </a>
  </div>
</div>
<?php endif; ?>

</div>

<!-- FOOTER -->
<footer class="text-center text-slate-500 mt-16 pb-4">
  <div class="flex items-center justify-center gap-2 mb-2">
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
    <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
  </div>
  <p>© <?= date('Y'); ?> <span class="text-amber-600 font-semibold brand-font">Aksara Jiwa</span> - Bookstore & Coffee</p>
  <p class="text-sm mt-1">Keranjang Belanja | <?= htmlspecialchars($user['nama']) ?></p>
</footer>

</main>
</div>

<script>
function incrementQty(button) {
  const input = button.parentElement.querySelector('input[name="qty"]');
  input.value = parseInt(input.value) + 1;
  input.dispatchEvent(new Event('change'));
}

function decrementQty(button) {
  const input = button.parentElement.querySelector('input[name="qty"]');
  if (parseInt(input.value) > 1) {
    input.value = parseInt(input.value) - 1;
    input.dispatchEvent(new Event('change'));
  }
}
</script>

</body>
</html>