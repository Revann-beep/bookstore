<?php
require '../auth/connection.php';

$id = $_POST['id_keranjang'];
$qty = $_POST['qty'];

mysqli_query($conn, "
  UPDATE keranjang SET qty='$qty' WHERE id_keranjang='$id'
");

header("Location: ../pembeli/halaman-keranjang.php");
