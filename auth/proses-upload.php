<?php
session_start();
require 'connection.php';

$id_user  = $_SESSION['id_user'] ?? null;
$id_order = $_POST['id_order'] ?? null;

if (!$id_user || !$id_order) {
    die("Akses tidak valid");
}

if (!isset($_FILES['bukti'])) {
    die("File tidak ditemukan");
}

$file = $_FILES['bukti'];

/* VALIDASI */
$extValid = ['jpg','jpeg','png','webp'];
$ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

if (!in_array($ext, $extValid)) {
    die("Format file tidak didukung");
}

$namaFile = 'BUKTI_' . time() . '.' . $ext;
$path = "../img/bukti/" . $namaFile;

move_uploaded_file($file['tmp_name'], $path);

/* UPDATE ORDER */
mysqli_query($conn, "
  UPDATE orders
  SET bukti_tf = '$namaFile',
      status = 'menunggu_verifikasi'
  WHERE id_order = '$id_order'
    AND id_pembeli = '$id_user'
");

/* REDIRECT BENAR */
header("Location: ../pembeli/invoice.php?id_order=$id_order");
exit;
