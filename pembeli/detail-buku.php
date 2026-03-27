<?php
session_start();
include '../auth/connection.php';

if(!isset($_GET['id'])){
    header("Location: index.php");
    exit;
}

$id = intval($_GET['id']);

$query = mysqli_query($conn,"
SELECT p.*, k.nama_kategori
FROM produk p
LEFT JOIN kategori k ON p.id_kategori = k.id_kategori
WHERE p.id_produk = '$id'
");

$data = mysqli_fetch_assoc($query);

if(!$data){
    echo "Buku tidak ditemukan";
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title><?= htmlspecialchars($data['nama_buku']) ?></title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-100">

<div class="max-w-6xl mx-auto p-6">

<a href="halaman-pesanan.php" 
   class="inline-flex items-center gap-2 mb-4 bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-lg text-sm">

   <i class="fas fa-arrow-left"></i> Kembali
</a>

<!-- CARD DETAIL -->
<div class="bg-white rounded-2xl shadow-lg p-8 grid md:grid-cols-2 gap-10">

<!-- GAMBAR -->
<div class="w-80 flex items-center justify-center">
    <img src="../img/produk/<?= $data['gambar'] ?>"
         class="w-full max-h-[420px] object-contain rounded-xl shadow">
</div>

<!-- DETAIL -->
<div class="flex flex-col justify-between">

<div>

<!-- KATEGORI -->
<span class="bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-sm">
<?= htmlspecialchars($data['nama_kategori']) ?>
</span>

<!-- NAMA BUKU -->
<h1 class="text-3xl font-bold mt-3 text-gray-800">
<?= htmlspecialchars($data['nama_buku']) ?>
</h1>

<!-- HARGA -->
<p class="text-2xl text-amber-600 font-bold mt-4">
Rp <?= number_format($data['harga'],0,',','.') ?>
</p>

<!-- STOK -->
<p class="mt-2 text-gray-600">
Stok tersedia :
<span class="font-semibold"><?= $data['stok'] ?></span>
</p>

<!-- DESKRIPSI -->
<div class="mt-6">
<h3 class="font-semibold text-lg mb-2">Deskripsi Buku</h3>

<p class="text-gray-600 leading-relaxed">
<?= nl2br(htmlspecialchars($data['deskripsi'])) ?>
</p>
</div>

</div>

<!-- BUTTON -->
<div class="flex gap-3 mt-8">

<a href="../auth/add-keranjang.php?id=<?= $data['id_produk'] ?>"
class="flex-1 text-center bg-amber-500 hover:bg-amber-600 text-white py-3 rounded-xl font-semibold transition">

Tambah ke Keranjang

</a>

<a href="pesan.php?id_penjual=<?= $data['id_penjual'] ?>&id_produk=<?= $data['id_produk'] ?>"
class="px-6 bg-blue-500 hover:bg-blue-600 text-white py-3 rounded-xl font-semibold rounded-xl">

Chat Penjual

</a>

</div>

</div>

</div>

</div>

</body>
</html>