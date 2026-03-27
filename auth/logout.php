<?php
session_start();
require 'connection.php';

// Jika user login ubah status offline
if (isset($_SESSION['id_user'])) {
    $id = $_SESSION['id_user'];
    mysqli_query($conn, "UPDATE users SET status='offline' WHERE id_user='$id'");
}

// Hapus semua session
$_SESSION = [];
session_destroy();

// Buat session baru
session_start();
session_regenerate_id(true);

// Redirect ke login
header("Location: ../index.php");
exit();
?>