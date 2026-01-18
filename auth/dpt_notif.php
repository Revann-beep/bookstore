<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header('Content-Type: application/json');
    echo json_encode(['unread_count' => 0]);
    exit;
}

$id_penjual = $_SESSION['id_user'];

// Hitung pesan belum dibaca
$result = mysqli_query($conn, "
    SELECT COUNT(*) as unread_count 
    FROM messages 
    WHERE receiver_id = '$id_penjual' 
    AND is_read = 0
");
$data = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode(['unread_count' => $data['unread_count'] ?? 0]);
?>