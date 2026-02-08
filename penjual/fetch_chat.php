<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user'])) {
    exit;
}

$penjual_id = $_SESSION['id_user'];
$chatWith   = (int)($_GET['user'] ?? 0);
$id_produk  = (int)($_GET['id_produk'] ?? 0);

if (!$chatWith || !$id_produk) exit;

$qChat = mysqli_query($conn, "
    SELECT * FROM messages
    WHERE id_produk='$id_produk'
    AND (
        (sender_id='$penjual_id' AND receiver_id='$chatWith')
        OR
        (sender_id='$chatWith' AND receiver_id='$penjual_id')
    )
    ORDER BY created_at ASC
");

$data = [];
while ($row = mysqli_fetch_assoc($qChat)) {
    $data[] = $row;
}

echo json_encode($data);
?>