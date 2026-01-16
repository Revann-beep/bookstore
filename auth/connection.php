<?php
date_default_timezone_set('Asia/Jakarta'); // Tambahkan ini paling atas

$conn = mysqli_connect("localhost", "root", "", "bookstore");
if (!$conn) {
    die("Koneksi gagal: " . mysqli_connect_error());
}
?>