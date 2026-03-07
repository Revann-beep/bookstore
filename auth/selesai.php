<?php
session_start();
require 'connection.php';

if(!isset($_SESSION['id_user'])){
    header("Location: ../auth/index.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_detail = (int)$_POST['id_detail'];

/* update status menjadi selesai */
mysqli_query($conn,"
UPDATE order_details od
JOIN orders o ON od.id_order=o.id_order
SET od.status_detail='selesai'
WHERE od.id_detail='$id_detail'
AND o.id_pembeli='$id_user'
");

header("Location: ../pembeli/status.php");
exit;