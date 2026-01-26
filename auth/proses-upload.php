<?php
session_start();
require 'connection.php';

$id_user    = $_SESSION['id_user'] ?? null;
$id_order   = $_POST['id_order'] ?? null;
$id_penjual = $_POST['id_penjual'] ?? null;

if (!$id_user || !$id_order || !$id_penjual) {
    die("Akses tidak valid");
}

if (!isset($_FILES['bukti'])) {
    die("File tidak ditemukan");
}

/* =======================
   VALIDASI FILE
======================= */
$file = $_FILES['bukti'];
$extValid = ['jpg','jpeg','png','webp','jfif'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $extValid)) {
    die("Format file tidak didukung");
}

$namaFile = 'BUKTI_' . time() . '_' . $id_penjual . '.' . $ext;
$path = "../img/bukti/" . $namaFile;

if (!move_uploaded_file($file['tmp_name'], $path)) {
    die("Upload gagal");
}

/* =======================
   AMBIL DATA ORDER
======================= */
$orderQ = mysqli_query($conn, "
    SELECT bukti_tf 
    FROM orders 
    WHERE id_order = '$id_order'
      AND id_pembeli = '$id_user'
");

$order = mysqli_fetch_assoc($orderQ);
if (!$order) {
    die("Order tidak ditemukan");
}

/* =======================
   DECODE BUKTI LAMA
======================= */
$bukti_arr = [];
if (!empty($order['bukti_tf'])) {
    $bukti_arr = json_decode($order['bukti_tf'], true);
}

/* CEGAH UPLOAD GANDA */
if (isset($bukti_arr[$id_penjual])) {
    header("Location: ../pembeli/invoice.php?id_order=$id_order");
    exit;
}

/* SIMPAN BUKTI BARU */
$bukti_arr[$id_penjual] = [
    'file'        => $namaFile,
    'uploaded_at'=> date('Y-m-d H:i:s'),
    'status'      => 'uploaded'
];

/* =======================
   HITUNG TOTAL PENJUAL
======================= */
$penjualQ = mysqli_query($conn, "
    SELECT DISTINCT p.id_penjual
    FROM order_details od
    JOIN produk p ON p.id_produk = od.id_produk
    WHERE od.id_order = '$id_order'
");

$total_penjual = mysqli_num_rows($penjualQ);
$total_upload  = count($bukti_arr);

/* =======================
   TENTUKAN STATUS
======================= */
$status_baru = ($total_upload >= $total_penjual)
    ? 'menunggu_verifikasi'
    : 'pending';

/* =======================
   UPDATE ORDER
======================= */
$json = json_encode($bukti_arr);

$update = mysqli_query($conn, "
    UPDATE orders
    SET bukti_tf = '$json',
        status   = '$status_baru'
    WHERE id_order = '$id_order'
      AND id_pembeli = '$id_user'
");

if (!$update) {
    die("Error MySQL: " . mysqli_error($conn));
}

/* =======================
   REDIRECT KE INVOICE
======================= */
header("Location: ../pembeli/invoice.php?id_order=$id_order");
exit;
