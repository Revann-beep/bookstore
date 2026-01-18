<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user   = $_SESSION['id_user'];
$id_produk = $_GET['id'] ?? null;

if (!$id_produk) {
    header("Location: halaman-pesanan.php");
    exit;
}

/* CEK PRODUK */
$produkQ = mysqli_query($conn, "
    SELECT stok FROM produk
    WHERE id_produk = '$id_produk'
    LIMIT 1
");

$produk = mysqli_fetch_assoc($produkQ);

if (!$produk || $produk['stok'] <= 0) {
    header("Location: halaman-pesanan.php?stok=habis");
    exit;
}

/* CEK KERANJANG */
$cekQ = mysqli_query($conn, "
    SELECT id_keranjang 
    FROM keranjang
    WHERE id_user = '$id_user'
      AND id_produk = '$id_produk'
");

if (mysqli_num_rows($cekQ) > 0) {
    mysqli_query($conn, "
        UPDATE keranjang 
        SET qty = qty + 1 
        WHERE id_user = '$id_user'
          AND id_produk = '$id_produk'
    ");
} else {
    mysqli_query($conn, "
        INSERT INTO keranjang (id_user, id_produk, qty)
        VALUES ('$id_user', '$id_produk', 1)
    ");
}

header("Location: ../pembeli/halaman-keranjang.php");
exit;
