<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header('Content-Type: application/json');
    echo json_encode(['success' => false]);
    exit;
}

$id_penjual = $_SESSION['id_user'];

// Update semua pesan menjadi read
mysqli_query($conn, "
    UPDATE messages 
    SET is_read = 1 
    WHERE receiver_id = '$id_penjual' 
    AND is_read = 0
");

echo json_encode(['success' => true]);
?>