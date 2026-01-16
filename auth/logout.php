<?php
session_start();

/*
  Jika kamu pakai status online/offline di database,
  aktifkan bagian ini
// */
 require 'connection.php';
 if (isset($_SESSION['id_user'])) {
     $id = $_SESSION['id_user'];
     mysqli_query($conn, "UPDATE users SET status='offline' WHERE id_user='$id'");
 }

// Hapus semua session
session_unset();
session_destroy();

// Redirect ke halaman login
header("Location: ../index.php");
exit;
