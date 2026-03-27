<?php
session_start();
require '../auth/connection.php';

// Cegah cache browser
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// ================= CEK LOGIN =================
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// ================= HANYA PEMBELI =================
if ($_SESSION['role'] !== 'pembeli') {
    die("Akses ditolak");
}

$pembeli_id   = $_SESSION['id_user'];
$pembeli_nama = $_SESSION['nama'];

// ================= OPSIONAL ID PRODUK =================
$id_produk = isset($_GET['id_produk']) ? intval($_GET['id_produk']) : null;

// ================= TENTUKAN PENJUAL =================
if ($id_produk) {
    // ===== CHAT LEWAT PRODUK =====
    $qProduk = mysqli_query($conn, "
        SELECT p.id_produk, p.nama_buku, 
               u.id_user AS id_penjual, u.nama AS nama_penjual
        FROM produk p
        JOIN users u ON p.id_penjual = u.id_user
        WHERE p.id_produk = '$id_produk'
    ");

    $produk = mysqli_fetch_assoc($qProduk);
    if (!$produk) die("Produk tidak ditemukan");

    $penjual_id   = $produk['id_penjual'];
    $penjual_nama = $produk['nama_penjual'];
    $nama_buku    = $produk['nama_buku'];

} else {

    // ===== CHAT LANGSUNG KE PENJUAL =====
    if (isset($_GET['id_penjual'])) {

        $penjual_id = intval($_GET['id_penjual']);

    } elseif (isset($_GET['user'])) {

        // dari notifikasi
        $penjual_id = intval($_GET['user']);

    } else {

        // ===== AUTO AMBIL CHAT TERAKHIR =====
        $lastChat = mysqli_query($conn, "
            SELECT 
                CASE 
                    WHEN sender_id = '$pembeli_id' THEN receiver_id
                    ELSE sender_id
                END AS penjual_id
            FROM messages
            WHERE sender_id='$pembeli_id' OR receiver_id='$pembeli_id'
            ORDER BY created_at DESC
            LIMIT 1
        ");

        $dataLast = mysqli_fetch_assoc($lastChat);

        if ($dataLast) {
            $penjual_id = $dataLast['penjual_id'];
        } else {
            $penjual_id = null;
        }
    }

    if ($penjual_id) {

        $qPenjual = mysqli_query($conn, "
            SELECT id_user, nama 
            FROM users 
            WHERE id_user='$penjual_id' AND role='penjual'
        ");

        $penjual = mysqli_fetch_assoc($qPenjual);

        if ($penjual) {
            $penjual_nama = $penjual['nama'];
        } else {
            $penjual_nama = "Penjual";
        }

    } else {

        $penjual_nama = null;
    }

    $nama_buku = null;
}

// ================= MARK AS READ =================
$whereProdukRead = $id_produk 
    ? "AND id_produk='$id_produk'" 
    : "AND id_produk IS NULL";

mysqli_query($conn, "
    UPDATE messages 
    SET is_read = 1 
    WHERE sender_id='$penjual_id'
      AND receiver_id='$pembeli_id'
      $whereProdukRead
");

// ================= KIRIM PESAN =================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    mysqli_query($conn, "
        INSERT INTO messages (sender_id, receiver_id, id_produk, message, is_read)
        VALUES (
            '$pembeli_id',
            '$penjual_id',
            " . ($id_produk ? "'$id_produk'" : "NULL") . ",
            '$pesan',
            0
        )
    ");

    if ($id_produk) {
        header("Location: pesan.php?id_produk=$id_produk");
    } else {
        header("Location: pesan.php?id_penjual=$penjual_id");
    }
    exit;
}

// ================= AMBIL CHAT HISTORY =================
$whereProdukChat = $id_produk 
    ? "m.id_produk='$id_produk'" 
    : "1=1";

$chatQuery = mysqli_query($conn, "
SELECT m.*, u.nama AS sender_nama, u.role AS sender_role
FROM messages m
JOIN users u ON u.id_user = m.sender_id
WHERE
(
    (m.sender_id='$pembeli_id' AND m.receiver_id='$penjual_id')
    OR
    (m.sender_id='$penjual_id' AND m.receiver_id='$pembeli_id')
)
ORDER BY m.created_at ASC
");

$chatHistory = [];
while ($row = mysqli_fetch_assoc($chatQuery)) {
    $chatHistory[] = $row;
}

// ================= AMBIL DAFTAR PENJUAL YANG PERNAH DI-CHAT =================
$daftarPenjualQuery = mysqli_query($conn, "
    SELECT DISTINCT 
        u.id_user,
        u.nama,
        u.image,
        (
            SELECT message 
            FROM messages m2 
            WHERE (m2.sender_id = u.id_user OR m2.receiver_id = u.id_user)
              AND (m2.sender_id = '$pembeli_id' OR m2.receiver_id = '$pembeli_id')
            ORDER BY m2.created_at DESC 
            LIMIT 1
        ) as last_message,
        (
            SELECT created_at 
            FROM messages m2 
            WHERE (m2.sender_id = u.id_user OR m2.receiver_id = u.id_user)
              AND (m2.sender_id = '$pembeli_id' OR m2.receiver_id = '$pembeli_id')
            ORDER BY m2.created_at DESC 
            LIMIT 1
        ) as last_message_time,
        (
            SELECT COUNT(*) 
            FROM messages m3 
            WHERE m3.sender_id = u.id_user 
              AND m3.receiver_id = '$pembeli_id'
              AND m3.is_read = 0
        ) as unread_count
    FROM users u
    INNER JOIN messages m ON (m.sender_id = u.id_user OR m.receiver_id = u.id_user)
    WHERE u.role = 'penjual'
      AND (m.sender_id = '$pembeli_id' OR m.receiver_id = '$pembeli_id')
    ORDER BY last_message_time DESC
");

$daftarPenjual = [];
while ($row = mysqli_fetch_assoc($daftarPenjualQuery)) {
    $daftarPenjual[] = $row;
}

// ================= UNREAD COUNT =================
$unreadQuery = mysqli_query($conn, "
    SELECT COUNT(*) AS unread_count
    FROM messages
    WHERE receiver_id='$pembeli_id'
      AND sender_id='$penjual_id'
      $whereProdukRead
      AND is_read=0
");

$unreadData  = mysqli_fetch_assoc($unreadQuery);
$unreadCount = $unreadData['unread_count'] ?? 0;
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Penjual - Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f9ff 0%, #e6f2f9 100%);
            min-height: 100vh;
        }

        .main-container {
            display: flex;
            height: 100vh;
            width: 100%;
            overflow: hidden;
        }

        /* Sidebar Styles - Biru Muda Segar */
        .sidebar-left {
            width: 380px;
            background: linear-gradient(180deg, #7FB5D1 0%, #9AC7E0 100%);
            border-right: 2px solid #4A8BB7;
            display: flex;
            flex-direction: column;
            overflow-y: auto;
            position: relative;
            box-shadow: 4px 0 20px rgba(74, 139, 183, 0.2);
            transition: transform 0.3s ease;
            z-index: 50;
        }

        /* Mobile Styles */
        @media (max-width: 768px) {
            .sidebar-left {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                z-index: 1000;
                width: 85%;
                max-width: 320px;
            }

            .sidebar-left.active {
                transform: translateX(0);
            }

            .chat-area {
                width: 100% !important;
            }
        }

        .mobile-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 999;
            backdrop-filter: blur(2px);
        }

        .mobile-overlay.active {
            display: block;
        }

        /* Chat Area */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: linear-gradient(135deg, #ffffff 0%, #f8fcff 100%);
            position: relative;
            overflow: hidden;
        }

        /* Decorative Elements - Biru Muda Theme */
        .book-icon {
            background: linear-gradient(135deg, #4A8BB7, #2D6A93);
            border: 2px solid #FFE7A0;
        }

        .coffee-icon {
            background: linear-gradient(135deg, #2D6A93, #1A4B6D);
            border: 2px solid #FFE7A0;
        }

        .online-indicator {
            width: 12px;
            height: 12px;
            background: #4ade80;
            border: 2px solid #FFE7A0;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.2); opacity: 0.7; }
            100% { transform: scale(1); opacity: 1; }
        }

        /* Navigation Items - Biru Muda Theme */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #1A4B6D;
            border-radius: 12px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            font-weight: 500;
        }

        .nav-item:hover {
            background: rgba(255, 231, 160, 0.3);
            transform: translateX(5px);
            color: #1A4B6D;
        }

        .nav-item.active {
            background: linear-gradient(90deg, #FFE7A0, #FFD966);
            color: #1A4B6D;
            border-left: 4px solid #4A8BB7;
            font-weight: 600;
        }

        .unread-badge {
            background: #FFD966;
            color: #1A4B6D;
            font-size: 11px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 999px;
            min-width: 20px;
            text-align: center;
            animation: bounce 1s infinite;
        }

        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-2px); }
        }

        /* Daftar Penjual Styles - Biru Muda Theme */
        .seller-list-container {
            max-height: 300px;
            overflow-y: auto;
            margin: 16px 0;
            padding: 0 4px;
        }

        .seller-list-container::-webkit-scrollbar {
            width: 4px;
        }

        .seller-list-container::-webkit-scrollbar-track {
            background: rgba(255, 231, 160, 0.2);
            border-radius: 10px;
        }

        .seller-list-container::-webkit-scrollbar-thumb {
            background: #4A8BB7;
            border-radius: 10px;
        }

        .seller-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            margin-bottom: 8px;
            background: rgba(255, 255, 255, 0.3);
            border: 1px solid rgba(74, 139, 183, 0.3);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
            position: relative;
        }

        .seller-item:hover {
            background: rgba(255, 231, 160, 0.3);
            transform: translateX(5px);
            border-color: #FFD966;
        }

        .seller-item.active {
            background: linear-gradient(90deg, #FFE7A0, #FFD966);
            border-color: #4A8BB7;
        }

        .seller-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #4A8BB7, #2D6A93);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 18px;
            position: relative;
            flex-shrink: 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .seller-avatar.small {
            width: 40px;
            height: 40px;
            font-size: 16px;
        }

        .seller-info {
            flex: 1;
            min-width: 0;
        }

        .seller-name {
            font-weight: 600;
            color: #1A4B6D;
            font-size: 14px;
            margin-bottom: 4px;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .seller-last-message {
            color: #2D6A93;
            font-size: 12px;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .message-time {
            font-size: 10px;
            color: #4A8BB7;
            margin-top: 2px;
        }

        .seller-unread-badge {
            background: #FFD966;
            color: #1A4B6D;
            font-size: 10px;
            font-weight: bold;
            padding: 2px 6px;
            border-radius: 999px;
            min-width: 18px;
            text-align: center;
            margin-left: 8px;
        }

        /* Chat Header */
        .chat-header {
            background: linear-gradient(135deg, #ffffff, #f0f9ff);
            border-bottom: 3px solid #7FB5D1;
            padding: 16px 24px;
            display: flex;
            align-items: center;
            position: relative;
            box-shadow: 0 4px 15px rgba(127, 181, 209, 0.1);
        }

        .status-badge {
            background: #4ade80;
            color: #1A4B6D;
            font-size: 10px;
            padding: 2px 8px;
            border-radius: 999px;
            font-weight: 600;
        }

        /* Quick Actions - Biru Muda Theme */
        .quick-action-btn {
            width: 100%;
            background: rgba(255, 231, 160, 0.3);
            border: 1px solid rgba(74, 139, 183, 0.3);
            color: #1A4B6D;
            padding: 8px 12px;
            border-radius: 10px;
            font-size: 12px;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            font-weight: 500;
        }

        .quick-action-btn:hover {
            background: rgba(255, 231, 160, 0.5);
            border-color: #FFD966;
            transform: translateX(5px);
        }

        /* Quick Replies */
        .quick-reply-btn {
            background: white;
            border: 1px solid #7FB5D1;
            color: #1A4B6D;
            padding: 8px 16px;
            border-radius: 999px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            white-space: nowrap;
            box-shadow: 0 2px 5px rgba(127, 181, 209, 0.1);
        }

        .quick-reply-btn:hover {
            background: #7FB5D1;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(127, 181, 209, 0.3);
        }

        /* Chat Messages */
        .chat-messages-container {
            flex: 1;
            overflow-y: auto;
            position: relative;
            background: radial-gradient(circle at 10% 20%, rgba(127, 181, 209, 0.05) 0%, transparent 30%);
        }

        .chat-messages-container::-webkit-scrollbar {
            width: 6px;
        }

        .chat-messages-container::-webkit-scrollbar-track {
            background: #f0f9ff;
        }

        .chat-messages-container::-webkit-scrollbar-thumb {
            background: #7FB5D1;
            border-radius: 10px;
        }

        .message-buyer {
            background: linear-gradient(135deg, #7FB5D1, #4A8BB7);
            border-radius: 20px 20px 0 20px;
            max-width: 70%;
            box-shadow: 0 4px 15px rgba(74, 139, 183, 0.2);
            position: relative;
        }

        .message-seller {
            background: white;
            border: 2px solid #FFE7A0;
            border-radius: 20px 20px 20px 0;
            max-width: 70%;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .time-badge {
            font-size: 10px;
            color: #9ca3af;
        }

        .date-separator {
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 20px 0;
        }

        .date-label {
            background: rgba(127, 181, 209, 0.1);
            padding: 5px 15px;
            border-radius: 999px;
            font-size: 11px;
            color: #1A4B6D;
            border: 1px solid #FFE7A0;
        }

        .empty-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100%;
            padding: 20px;
        }

        .book-decoration {
            position: absolute;
            font-size: 24px;
            opacity: 0.1;
            transform: rotate(-10deg);
            pointer-events: none;
        }

        .page-turn {
            animation: pageTurn 4s infinite;
        }

        @keyframes pageTurn {
            0%, 100% { transform: rotate(-5deg); }
            50% { transform: rotate(5deg); }
        }

        /* Input Area */
        .input-container {
            background: white;
            border-top: 3px solid #7FB5D1;
            padding: 20px 24px;
            box-shadow: 0 -4px 15px rgba(127, 181, 209, 0.1);
        }

        .message-input {
            flex: 1;
            background: #f0f9ff;
            border: 2px solid #d4e6f2;
            border-radius: 999px;
            padding: 12px 20px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .message-input:focus {
            outline: none;
            border-color: #7FB5D1;
            box-shadow: 0 0 0 4px rgba(127, 181, 209, 0.1);
        }

        .send-btn {
            background: linear-gradient(135deg, #7FB5D1, #4A8BB7);
            color: white;
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
        }

        .send-btn:hover {
            transform: scale(1.1) rotate(15deg);
            box-shadow: 0 4px 15px rgba(74, 139, 183, 0.4);
        }

        /* Message Animation */
        .message-animation {
            opacity: 0;
            transform: translateY(20px) scale(0.9);
            animation: messageIn 0.3s ease forwards;
        }

        @keyframes messageIn {
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        /* Section Title - Biru Muda Theme */
        .section-title {
            color: #1A4B6D;
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
            border-bottom: 2px solid rgba(255, 231, 160, 0.5);
            padding-bottom: 8px;
        }

        .section-title i {
            color: #FFD966;
            font-size: 18px;
        }

        /* Brand Font */
        .brand-font {
            font-family: 'Playfair Display', serif;
        }

        /* Text colors for sidebar */
        .text-blue-100 {
            color: #E6F3FF !important;
        }

        .text-blue-200 {
            color: #BFE0FF !important;
        }

        .text-blue-300 {
            color: #99CCFF !important;
        }

        .text-blue-600 {
            color: #1A4B6D !important;
        }

        .text-blue-700 {
            color: #2D6A93 !important;
        }

        .bg-blue-900 {
            background-color: #1A4B6D !important;
        }

        .from-blue-900 {
            --tw-gradient-from: #7FB5D1 !important;
        }

        .to-blue-800 {
            --tw-gradient-to: #4A8BB7 !important;
        }

        .border-blue-700 {
            border-color: #FFE7A0 !important;
        }

        .border-blue-900 {
            border-color: #4A8BB7 !important;
        }

        .hover\:text-blue-200:hover {
            color: #BFE0FF !important;
        }

        .text-blue-400 {
            color: #99CCFF !important;
        }

        .from-blue-50 {
            --tw-gradient-from: #E6F3FF !important;
        }

        .to-indigo-50 {
            --tw-gradient-to: #E0EEFF !important;
        }

        .border-blue-100 {
            border-color: #BFE0FF !important;
        }

        .bg-gradient-to-r.from-blue-600.to-blue-700 {
            background: linear-gradient(135deg, #7FB5D1, #033d63) !important;
        }
    </style>
</head>
<body>
    <div class="main-container">
        
        <!-- SIDEBAR LEFT -->
        <div class="sidebar-left" id="sidebar">
            <!-- Header -->
            <div class="p-6 border-b border-blue-400/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-14 h-14 book-icon rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-book-open text-2xl text-blue-100"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold brand-font text-blue-100 mb-1">AKSARA JIWA</h1>
                        <p class="text-xs text-blue-200/80">Bookstore</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-gradient-to-r from-blue-400/30 to-blue-300/20 p-4 rounded-xl mb-6 border border-blue-200/30">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 coffee-icon rounded-full flex items-center justify-center shadow">
                                <i class="fas fa-user text-lg text-blue-100"></i>
                            </div>
                            <div class="online-indicator"></div>
                        </div>
                        <div>
                            <p class="font-medium text-blue-100"><?= htmlspecialchars($pembeli_nama) ?></p>
                            <p class="text-xs text-blue-200/80">Pembeli Aktif</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Daftar Penjual yang Pernah di-Chat -->
            <div class="px-6 py-4 border-b border-blue-400/30">
                <div class="section-title">
                    <i class="fas fa-store"></i>
                    <span>Daftar Penjual</span>
                </div>
                
                <div class="seller-list-container">
                    <?php if(empty($daftarPenjual)): ?>
                        <div class="text-center py-8">
                            <div class="w-16 h-16 mx-auto bg-blue-400/20 rounded-full flex items-center justify-center mb-3">
                                <i class="fas fa-comment-slash text-2xl text-blue-600/50"></i>
                            </div>
                            <p class="text-blue-600/70 text-sm">Belum ada riwayat chat dengan penjual</p>
                            <p class="text-blue-600/50 text-xs mt-2">Mulai chat dengan penjual dari halaman produk</p>
                        </div>
                    <?php else: ?>
                        <?php foreach($daftarPenjual as $penjual): ?>
                            <a href="pesan.php?id_penjual=<?= $penjual['id_user'] ?>" class="text-decoration-none">
                                <div class="seller-item <?= ($penjual['id_user'] == $penjual_id) ? 'active' : '' ?>">
                                    <div class="seller-avatar small">
                                        <?php 
                                        // Cek apakah ada foto profil
                                        if(!empty($penjual['image']) && file_exists('../uploads/users/' . $penjual['image'])): 
                                        ?>
                                            <img src="../uploads/users/<?= $penjual['image'] ?>" 
                                                 alt="Profile" 
                                                 class="w-full h-full object-cover rounded-lg">
                                        <?php else: ?>
                                            <?= strtoupper(substr($penjual['nama'], 0, 1)) ?>
                                        <?php endif; ?>
                                    </div>
                                    <div class="seller-info">
                                        <div class="seller-name">
                                            <?= htmlspecialchars($penjual['nama']) ?>
                                            <?php if($penjual['unread_count'] > 0): ?>
                                                <span class="seller-unread-badge"><?= $penjual['unread_count'] ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <?php if($penjual['last_message']): ?>
                                            <div class="seller-last-message">
                                                <?= htmlspecialchars(substr($penjual['last_message'], 0, 30)) ?>...
                                            </div>
                                            <div class="message-time">
                                                <?= date('H:i', strtotime($penjual['last_message_time'])) ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <i class="fas fa-chevron-right text-blue-400 text-xs"></i>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="flex-1 p-4">
                <nav class="space-y-1">
                    <a href="dashboard_pembeli.php" class="nav-item">
                        <i class="fas fa-home w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="halaman-pesanan.php" class="nav-item">
                        <i class="fas fa-shopping-cart w-5 text-center"></i>
                        <span>Produk</span>
                    </a>
                    
                    <a href="status.php" class="nav-item">
                        <i class="fas fa-truck-fast w-5 text-center"></i>
                        <span>Status</span>
                    </a>
                    
                    <a href="pesan.php" class="nav-item active">
                        <i class="fas fa-comments w-5 text-center"></i>
                        <span>Chat Penjual</span>
                    </a>
                    
                    <a href="my.php" class="nav-item">
                        <i class="fas fa-user-cog w-5 text-center"></i>
                        <span>Akun Saya</span>
                    </a>
                </nav>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-blue-600 mb-3 px-4">Pesan Cepat</h3>
                    <div class="space-y-2 px-4">
                        <button onclick="quickMessage('Apakah buku ini tersedia stoknya? 📚')" 
                                class="quick-action-btn">
                            <i class="fas fa-boxes text-blue-600 mr-2"></i>
                            Tanya Ketersediaan
                        </button>
                        
                        <button onclick="quickMessage('Berapa estimasi pengiriman ke lokasi saya? 🚚')" 
                                class="quick-action-btn">
                            <i class="fas fa-clock text-blue-600 mr-2"></i>
                            Tanya Estimasi
                        </button>
                    </div>
                </div>
                
                <!-- Book Recommendations -->
                <div class="mt-6 px-4">
                    <h3 class="text-sm font-semibold text-blue-600 mb-2">Rekomendasi Buku</h3>
                    <div class="text-xs text-blue-600/70">
                        Tanyakan tentang koleksi buku fiksi, non-fiksi, atau buku terbaru kami!
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t border-blue-400/30">
                <a href="../auth/logout.php" class="nav-item text-red-400 hover:text-red-300">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Keluar</span>
                </a>
            </div>
        </div>
        
        <!-- MOBILE OVERLAY -->
        <div class="mobile-overlay" id="mobileOverlay" onclick="toggleSidebar()"></div>
        
        <!-- CHAT AREA RIGHT -->
        <div class="chat-area">
            
            <!-- Chat Header -->
            <div class="chat-header">
                <!-- Mobile Menu Button -->
                <button class="md:hidden mr-4 text-blue-600" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Seller Info -->
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div class="w-14 h-14 book-icon rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-store text-xl text-blue-100"></i>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($penjual_nama) ?></h2>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-blue-600">Penjual Aksara Jiwa</span>
                            <span class="status-badge">Online</span>
                        </div>
                    </div>
                </div>
                
                <!-- Store Info -->
                <div class="hidden md:flex items-center gap-2 text-blue-600">
                    <i class="fas fa-store"></i>
                    <span class="text-sm font-medium">Bookstore</span>
                </div>
            </div>
            
            <!-- Quick Replies -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 border-b border-blue-100 py-3 px-6">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="quickMessage('Halo, saya tertarik dengan koleksi buku di Aksara Jiwa! 📚')" 
                            class="quick-reply-btn">
                        <i class="fas fa-hand-sparkles mr-2"></i>Halo
                    </button>
                    
                    <button onclick="quickMessage('Bisa rekomendasikan buku fiksi terbaik?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-star mr-2"></i>Rekomendasi
                    </button>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div class="chat-messages-container scrollbar-thin p-6">
                <!-- Book Decorations -->
                <div class="book-decoration" style="top: 10%; left: 10%;">📖</div>
                <div class="book-decoration" style="top: 20%; right: 15%;">☕</div>
                <div class="book-decoration page-turn" style="bottom: 15%; left: 15%;">📚</div>
                
                <?php if(empty($chatHistory)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="w-24 h-24 bg-gradient-to-br from-blue-100 to-indigo-100 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <i class="fas fa-comments text-4xl text-blue-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Mulai Percakapan</h3>
                        <p class="text-blue-600 text-center max-w-sm mb-8">
                            Mulai obrolan dengan penjual <span class="font-semibold"><?= htmlspecialchars($penjual_nama) ?></span> untuk menanyakan buku, stok, atau informasi lainnya tentang koleksi Aksara Jiwa.
                        </p>
                        <button onclick="quickMessage('Halo, saya ingin bertanya tentang koleksi buku di Aksara Jiwa!')" 
                                class="bg-gradient-to-r from-blue-400 to-blue-500 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all shadow-lg hover:scale-105">
                            <i class="fas fa-comment-medical mr-2"></i>Mulai Chat
                        </button>
                    </div>
                <?php else: ?>
                    <!-- Chat Messages -->
                    <?php 
                    $lastDate = null;
                    foreach ($chatHistory as $index => $m): 
                        $messageDate = date('Y-m-d', strtotime($m['created_at']));
                        $today = date('Y-m-d');
                        $yesterday = date('Y-m-d', strtotime('-1 day'));
                        
                        $displayDate = '';
                        if ($messageDate == $today) {
                            $displayDate = 'Hari ini';
                        } elseif ($messageDate == $yesterday) {
                            $displayDate = 'Kemarin';
                        } else {
                            $displayDate = date('d M Y', strtotime($m['created_at']));
                        }
                        
                        if ($lastDate != $messageDate):
                    ?>
                        <div class="date-separator">
                            <span class="date-label">📅 <?= $displayDate ?></span>
                        </div>
                    <?php 
                        endif;
                        $lastDate = $messageDate;
                    ?>
                    
                    <!-- Message -->
                    <div class="message-animation mb-6">
                        <?php if ($m['sender_id'] == $pembeli_id): ?>
                            <!-- Buyer Message (Right) -->
                            <div class="flex justify-end">
                                <div class="message-buyer px-5 py-4">
                                    <div class="text-white text-sm font-medium">
                                        <?= htmlspecialchars($m['message']) ?>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 mt-3">
                                        <span class="time-badge text-blue-200">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </span>
                                        <span class="text-xs text-blue-200 opacity-80">
                                            <i class="fas fa-check-double"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Seller Message (Left) -->
                            <div class="flex">
                                <div class="message-seller px-5 py-4">
                                    <div class="text-slate-800 text-sm font-medium">
                                        <?= htmlspecialchars($m['message']) ?>
                                    </div>
                                    <div class="flex items-center gap-2 mt-3">
                                        <span class="time-badge">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
            
            <!-- Input Area -->
            <form method="POST" class="input-container">
                <div class="flex items-center gap-3 w-full">
                    <input type="text" 
                           name="pesan" 
                           required
                           placeholder="Tulis pesan untuk penjual <?= htmlspecialchars($penjual_nama) ?>..."
                           class="message-input"
                           id="messageInput"
                           autocomplete="off">
                    
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-blue-600 hover:text-blue-700 transition-colors">
                        <i class="fas fa-smile text-lg"></i>
                    </button>
                    
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane text-lg"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Toggle sidebar for mobile
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            sidebar.classList.toggle('active');
            overlay.classList.toggle('active');
            
            if (sidebar.classList.contains('active')) {
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }
        
        // Quick message function
        function quickMessage(text) {
            const input = document.getElementById('messageInput');
            input.value = text;
            input.focus();
            
            // Animation effect
            input.style.transform = 'scale(1.02)';
            input.style.boxShadow = '0 0 0 3px rgba(127, 181, 209, 0.3)';
            setTimeout(() => {
                input.style.transform = '';
                input.style.boxShadow = '';
            }, 300);
        }
        
        // Auto scroll to bottom
        function scrollToBottom() {
            const container = document.querySelector('.chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to bottom
            scrollToBottom();
            
            // Add animation to existing messages
            const messages = document.querySelectorAll('.message-animation');
            messages.forEach((msg, index) => {
                setTimeout(() => {
                    msg.style.opacity = '1';
                    msg.style.transform = 'translateY(0) scale(1)';
                }, index * 50);
            });
            
            // Handle Enter key to send
            const messageInput = document.getElementById('messageInput');
            if (messageInput) {
                messageInput.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter' && !e.shiftKey) {
                        e.preventDefault();
                        document.querySelector('form').submit();
                    }
                });
            }
            
            // Auto-resize textarea (if needed)
            if (messageInput && messageInput.tagName === 'TEXTAREA') {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        });
        
        // Submit form handler
        const form = document.querySelector('form');
        if (form) {
            form.addEventListener('submit', function(e) {
                // Add sending animation
                const sendBtn = this.querySelector('.send-btn');
                if (sendBtn) {
                    const originalHtml = sendBtn.innerHTML;
                    sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                    
                    setTimeout(() => {
                        sendBtn.innerHTML = originalHtml;
                    }, 1000);
                }
                
                // Scroll to bottom after sending
                setTimeout(scrollToBottom, 100);
            });
        }
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth < 768 && 
                sidebar && 
                sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !e.target.closest('.md\\:hidden')) {
                toggleSidebar();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus input
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                const input = document.getElementById('messageInput');
                if (input) input.focus();
            }
            
            // Escape to clear input
            if (e.key === 'Escape') {
                const input = document.getElementById('messageInput');
                if (input) input.value = '';
            }
        });
        
        // Auto-refresh for new messages (simplified)
        setInterval(() => {
            fetch(window.location.href)
                .then(response => response.text())
                .then(html => {
                    const parser = new DOMParser();
                    const newDoc = parser.parseFromString(html, 'text/html');
                    const newMessages = newDoc.querySelectorAll('.message-animation');
                    const currentMessages = document.querySelectorAll('.message-animation');
                    
                    if (newMessages.length > currentMessages.length) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 3000);
    </script>
</body>
</html>