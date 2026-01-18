<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    die("Akses ditolak");
}

$id_penjual = $_SESSION['id_user'];
$id_order   = $_GET['id_order'] ?? null;

if (!$id_order) {
    die("ID order tidak valid");
}

/* CEK ORDER + WAKTU REFUND */
$q = mysqli_query($conn, "
    SELECT o.id_order, o.refund_at
    FROM orders o
    JOIN order_items oi ON o.id_order = oi.id_order
    JOIN produk p ON oi.id_produk = p.id_produk
    WHERE o.id_order = '$id_order'
      AND o.status = 'refund'
      AND p.id_penjual = '$id_penjual'
    LIMIT 1
");

$data = mysqli_fetch_assoc($q);

if (!$data) {
    die("Order tidak bisa dihapus");
}

/* CEK 1 MENIT */
$refund_time = strtotime($data['refund_at']);
if (time() - $refund_time < 60) {
    die("Menunggu 1 menit sebelum dihapus");
}

/* HAPUS */
mysqli_query($conn, "DELETE FROM order_items WHERE id_order = '$id_order'");
mysqli_query($conn, "DELETE FROM orders WHERE id_order = '$id_order'");

header("Location: laporan.php");
exit;
