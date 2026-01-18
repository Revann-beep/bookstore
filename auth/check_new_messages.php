<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header('Content-Type: application/json');
    echo json_encode(['has_new_messages' => false]);
    exit;
}

$pembeli_id = $_SESSION['id_user'];

// Cek apakah ada pesan baru dari penjual
$result = mysqli_query($conn, "
    SELECT COUNT(*) as new_count 
    FROM messages 
    WHERE receiver_id = '$pembeli_id' 
    AND is_read = 0
    AND sender_id IN (SELECT id_user FROM users WHERE role = 'penjual')
");
$data = mysqli_fetch_assoc($result);

header('Content-Type: application/json');
echo json_encode([
    'has_new_messages' => ($data['new_count'] ?? 0) > 0,
    'new_count' => $data['new_count'] ?? 0
]);
?>