<?php
session_start();
require '../auth/connection.php';

// CEK LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// HANYA PEMBELI YANG BOLEH CHAT
if ($_SESSION['role'] !== 'pembeli') {
    die("Akses ditolak");
}

$pembeli_id = $_SESSION['id_user'];
$pembeli_nama = $_SESSION['nama'];

// AMBIL SEMUA PENJUAL (dalam kasus ini ambil pertama/utama)
$qPenjual = mysqli_query($conn, "
    SELECT id_user, nama 
    FROM users 
    WHERE role = 'penjual' 
    ORDER BY id_user ASC
    LIMIT 1
");

$penjual = mysqli_fetch_assoc($qPenjual);

if (!$penjual) {
    die("Penjual tidak ditemukan");
}

$penjual_id = $penjual['id_user'];
$penjual_nama = $penjual['nama'];

// MARK AS READ untuk pesan dari penjual
mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE sender_id = '$penjual_id' AND receiver_id = '$pembeli_id' AND is_read = 0");

// KIRIM PESAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan'])) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    
    mysqli_query($conn, "
        INSERT INTO messages (sender_id, receiver_id, message, is_read)
        VALUES ('$pembeli_id', '$penjual_id', '$pesan', 0)
    ");
    
    // Redirect untuk refresh
    header("Location: pesan.php");
    exit;
}

// AMBIL CHAT HISTORY
$chatQuery = mysqli_query($conn, "
    SELECT m.*, u.nama as sender_nama, u.role as sender_role
    FROM messages m
    JOIN users u ON u.id_user = m.sender_id
    WHERE (sender_id='$pembeli_id' AND receiver_id='$penjual_id')
       OR (sender_id='$penjual_id' AND receiver_id='$pembeli_id')
    ORDER BY created_at ASC
");

$chatHistory = [];
while ($row = mysqli_fetch_assoc($chatQuery)) {
    $chatHistory[] = $row;
}

// AMBIL PESAN BELUM DIBACA UNTUK NOTIFIKASI
$unreadQuery = mysqli_query($conn, "
    SELECT COUNT(*) as unread_count 
    FROM messages 
    WHERE receiver_id = '$pembeli_id' 
    AND sender_id = '$penjual_id'
    AND is_read = 0
");
$unreadData = mysqli_fetch_assoc($unreadQuery);
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
        :root {
            --primary-amber: #d97706;
            --primary-slate: #1e293b;
            --accent-coffee: #7c2d12;
            --sidebar-width: 320px;
            --chat-header-height: 80px;
            --input-height: 80px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: #f8fafc;
            height: 100vh;
            overflow: hidden;
        }
        
        .brand-font {
            font-family: 'Playfair Display', serif;
        }
        
        /* HORIZONTAL LAYOUT */
        .main-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* SIDEBAR LEFT - BOOKSTORE THEME */
        .sidebar-left {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #0f172a 0%, #1e293b 100%);
            border-right: 1px solid #334155;
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.2);
            z-index: 40;
        }
        
        /* CHAT AREA RIGHT */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
            background: linear-gradient(135deg, #fef3c7 0%, #fefce8 100%);
        }
        
        /* CHAT MESSAGES */
        .chat-messages-container {
            flex: 1;
            overflow-y: auto;
            background: #fefce8;
            background-image: 
                radial-gradient(#fde68a 1px, transparent 1px),
                radial-gradient(#fde68a 1px, transparent 1px);
            background-size: 50px 50px;
            background-position: 0 0, 25px 25px;
            background-attachment: fixed;
        }
        
        /* MESSAGE STYLES - BOOK THEME */
        .message-seller {
            background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid #e2e8f0;
            border-radius: 20px 20px 20px 6px;
            max-width: 70%;
            margin-right: auto;
            position: relative;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            border-left: 4px solid #d97706;
        }
        
        .message-seller::before {
            content: '📚';
            position: absolute;
            left: -35px;
            top: 10px;
            font-size: 16px;
            background: #ffffff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #d97706;
        }
        
        .message-buyer {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            border-radius: 20px 20px 6px 20px;
            max-width: 70%;
            margin-left: auto;
            position: relative;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.25);
            border-right: 4px solid #92400e;
        }
        
        .message-buyer::before {
            content: '☕';
            position: absolute;
            right: -35px;
            top: 10px;
            font-size: 16px;
            background: #ffffff;
            border-radius: 50%;
            width: 28px;
            height: 28px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #d97706;
        }
        
        /* HEADERS */
        .chat-header {
            height: var(--chat-header-height);
            background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%);
            border-bottom: 1px solid #fde68a;
            display: flex;
            align-items: center;
            padding: 0 24px;
            box-shadow: 0 2px 15px rgba(217, 119, 6, 0.1);
        }
        
        .input-container {
            height: var(--input-height);
            background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%);
            border-top: 1px solid #fde68a;
            display: flex;
            align-items: center;
            padding: 0 24px;
        }
        
        /* SCROLLBAR */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(217, 119, 6, 0.05);
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, #d97706 0%, #b45309 100%);
            border-radius: 3px;
        }
        
        /* ONLINE INDICATOR */
        .online-indicator {
            width: 10px;
            height: 10px;
            background: #10b981;
            border-radius: 50%;
            position: absolute;
            bottom: 2px;
            right: 2px;
            border: 2px solid white;
            animation: pulse 2s infinite;
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.4); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        
        /* DATE SEPARATOR */
        .date-separator {
            text-align: center;
            margin: 24px 0;
            position: relative;
        }
        
        .date-separator::before {
            content: '';
            position: absolute;
            left: 0;
            top: 50%;
            right: 0;
            height: 1px;
            background: linear-gradient(90deg, transparent, #d97706, transparent);
            z-index: 1;
        }
        
        .date-label {
            display: inline-block;
            background: #ffffff;
            padding: 6px 20px;
            border-radius: 20px;
            font-size: 12px;
            color: #78350f;
            border: 1px solid #fbbf24;
            position: relative;
            z-index: 2;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1);
        }
        
        /* QUICK ACTIONS */
        .quick-action-btn {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(251, 191, 36, 0.2);
            color: #fef3c7;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s;
            text-align: left;
            width: 100%;
            backdrop-filter: blur(10px);
        }
        
        .quick-action-btn:hover {
            background: rgba(251, 191, 36, 0.2);
            border-color: #fbbf24;
            transform: translateY(-2px);
        }
        
        /* QUICK REPLIES */
        .quick-reply-btn {
            background: #ffffff;
            border: 1px solid #fbbf24;
            color: #92400e;
            padding: 10px 18px;
            border-radius: 25px;
            font-size: 13px;
            transition: all 0.3s;
            white-space: nowrap;
            font-weight: 500;
            box-shadow: 0 2px 8px rgba(217, 119, 6, 0.1);
        }
        
        .quick-reply-btn:hover {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.2);
        }
        
        /* NAVIGATION */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 16px;
            color: #cbd5e1;
            border-radius: 10px;
            transition: all 0.3s;
        }
        
        .nav-item:hover {
            background: rgba(251, 191, 36, 0.1);
            color: #fef3c7;
            transform: translateX(5px);
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(217, 119, 6, 0.3);
            transform: translateX(5px);
        }
        
        /* INPUT FIELD */
        .message-input {
            flex: 1;
            background: #ffffff;
            border: 2px solid #fde68a;
            border-radius: 25px;
            padding: 14px 22px;
            font-size: 14px;
            transition: all 0.3s;
            color: #1e293b;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #d97706;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(217, 119, 6, 0.15);
        }
        
        /* SEND BUTTON */
        .send-btn {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s;
            margin-left: 12px;
            box-shadow: 0 4px 12px rgba(217, 119, 6, 0.3);
        }
        
        .send-btn:hover {
            transform: translateY(-2px) scale(1.05);
            box-shadow: 0 6px 20px rgba(217, 119, 6, 0.4);
        }
        
        /* BOOK STORE THEME ELEMENTS */
        .book-icon {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
        }
        
        .coffee-icon {
            background: linear-gradient(135deg, #7c2d12 0%, #9a3412 100%);
        }
        
        .brand-gradient {
            background: linear-gradient(135deg, #fbbf24 0%, #d97706 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        
        /* MOBILE RESPONSIVE */
        @media (max-width: 768px) {
            .sidebar-left {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                width: 300px;
                z-index: 50;
            }
            
            .sidebar-left.active {
                transform: translateX(0);
            }
            
            .mobile-overlay {
                display: none;
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(15, 23, 42, 0.8);
                z-index: 45;
                backdrop-filter: blur(4px);
            }
            
            .mobile-overlay.active {
                display: block;
            }
        }
        
        /* ANIMATIONS */
        .message-animation {
            animation: messageAppear 0.4s ease-out;
        }
        
        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(15px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* BADGE */
        .unread-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
            animation: bounce 2s infinite;
            box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
        }
        
        @keyframes bounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        /* EMPTY STATE */
        .empty-state {
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 40px;
            text-align: center;
        }
        
        /* BOOK STORE CHAT DECORATIONS */
        .book-decoration {
            position: absolute;
            font-size: 24px;
            opacity: 0.1;
            z-index: 0;
        }
        
        .page-turn {
            animation: pageTurn 3s ease-in-out infinite;
        }
        
        @keyframes pageTurn {
            0%, 100% { transform: rotateY(0deg); }
            50% { transform: rotateY(10deg); }
        }
        
        /* STATUS BADGES */
        .status-badge {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
            font-size: 11px;
            padding: 4px 12px;
            border-radius: 15px;
            font-weight: 600;
        }
        
        .time-badge {
            background: rgba(255, 255, 255, 0.9);
            color: #78350f;
            padding: 2px 8px;
            border-radius: 10px;
            font-size: 11px;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="main-container">
        
        <!-- SIDEBAR LEFT -->
        <div class="sidebar-left" id="sidebar">
            <!-- Header -->
            <div class="p-6 border-b border-amber-900/30">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-14 h-14 book-icon rounded-xl flex items-center justify-center shadow-lg">
                        <i class="fas fa-book-open text-2xl text-amber-100"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold brand-font text-amber-100 mb-1">AKSARA JIWA</h1>
                        <p class="text-xs text-amber-300/80">Bookstore & Coffee</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-gradient-to-r from-amber-900/30 to-amber-800/20 p-4 rounded-xl mb-6 border border-amber-700/30">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 coffee-icon rounded-full flex items-center justify-center shadow">
                                <i class="fas fa-user text-lg text-amber-100"></i>
                            </div>
                            <div class="online-indicator"></div>
                        </div>
                        <div>
                            <p class="font-medium text-amber-100"><?= htmlspecialchars($pembeli_nama) ?></p>
                            <p class="text-xs text-amber-300/80">Pembeli Aktif</p>
                        </div>
                    </div>
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
                        <span>Pesanan</span>
                    </a>
                    
                    <a href="status.php" class="nav-item">
                        <i class="fas fa-truck-fast w-5 text-center"></i>
                        <span>Status</span>
                    </a>
                    
                    <a href="pesan.php" class="nav-item active">
                        <i class="fas fa-comments w-5 text-center"></i>
                        <span>Chat Penjual</span>
                        <?php if($unreadCount > 0): ?>
                            <span class="unread-badge ml-auto"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="my.php" class="nav-item">
                        <i class="fas fa-user-cog w-5 text-center"></i>
                        <span>Akun Saya</span>
                    </a>
                </nav>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-amber-300 mb-3 px-4">Pesan Cepat</h3>
                    <div class="space-y-2 px-4">
                        <button onclick="quickMessage('Apakah buku ini tersedia stoknya? 📚')" 
                                class="quick-action-btn">
                            <i class="fas fa-boxes text-amber-300 mr-2"></i>
                            Tanya Ketersediaan
                        </button>
                        
                        <button onclick="quickMessage('Berapa estimasi pengiriman ke lokasi saya? 🚚')" 
                                class="quick-action-btn">
                            <i class="fas fa-clock text-amber-300 mr-2"></i>
                            Tanya Estimasi
                        </button>
                    </div>
                </div>
                
                <!-- Book Recommendations -->
                <div class="mt-6 px-4">
                    <h3 class="text-sm font-semibold text-amber-300 mb-2">Rekomendasi Buku</h3>
                    <div class="text-xs text-amber-300/70">
                        Tanyakan tentang koleksi buku fiksi, non-fiksi, atau buku kopi terbaru kami!
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t border-amber-900/30">
                <a href="../auth/logout.php" class="nav-item text-red-300 hover:text-red-200">
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
                <button class="md:hidden mr-4 text-amber-700" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Seller Info -->
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div class="w-14 h-14 book-icon rounded-full flex items-center justify-center shadow-lg">
                            <i class="fas fa-store text-xl text-amber-100"></i>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div>
                        <h2 class="font-bold text-slate-800 text-lg"><?= htmlspecialchars($penjual_nama) ?></h2>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-amber-700">Penjual Aksara Jiwa</span>
                            <span class="status-badge">Online</span>
                        </div>
                    </div>
                </div>
                
                <!-- Store Info -->
                <div class="hidden md:flex items-center gap-2 text-amber-700">
                    <i class="fas fa-coffee"></i>
                    <span class="text-sm font-medium">Bookstore & Coffee</span>
                </div>
            </div>
            
            <!-- Quick Replies -->
            <div class="bg-gradient-to-r from-amber-50 to-yellow-50 border-b border-amber-100 py-3 px-6">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="quickMessage('Halo, saya tertarik dengan koleksi buku di Aksara Jiwa! 📚')" 
                            class="quick-reply-btn">
                        <i class="fas fa-hand-sparkles mr-2"></i>Halo
                    </button>
                    
                    <button onclick="quickMessage('Apakah One Piece Volume terbaru tersedia?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-book mr-2"></i>Stok One Piece
                    </button>
                    
                    <button onclick="quickMessage('Bisa rekomendasikan buku fiksi terbaik?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-star mr-2"></i>Rekomendasi
                    </button>
                    
                    <button onclick="quickMessage('Apakah ada promo untuk member? ☕')" 
                            class="quick-reply-btn">
                        <i class="fas fa-percent mr-2"></i>Promo
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
                        <div class="w-24 h-24 bg-gradient-to-br from-amber-100 to-yellow-100 rounded-full flex items-center justify-center mb-6 shadow-lg">
                            <i class="fas fa-comments text-4xl text-amber-600"></i>
                        </div>
                        <h3 class="text-xl font-bold text-slate-800 mb-3">Mulai Percakapan</h3>
                        <p class="text-amber-700 text-center max-w-sm mb-8">
                            Mulai obrolan dengan penjual <span class="font-semibold"><?= htmlspecialchars($penjual_nama) ?></span> untuk menanyakan buku, stok, atau informasi lainnya tentang koleksi Aksara Jiwa.
                        </p>
                        <button onclick="quickMessage('Halo, saya ingin bertanya tentang koleksi buku di Aksara Jiwa!')" 
                                class="bg-gradient-to-r from-amber-500 to-amber-600 text-white px-8 py-3 rounded-full font-semibold hover:shadow-lg transition-all shadow-lg hover:scale-105">
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
                                        <span class="time-badge">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </span>
                                        <span class="text-xs text-amber-200 opacity-80">
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
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-amber-600 hover:text-amber-700 transition-colors">
                        <i class="fas fa-paperclip text-lg"></i>
                    </button>
                    
                    <input type="text" 
                           name="pesan" 
                           required
                           placeholder="Tulis pesan untuk penjual <?= htmlspecialchars($penjual_nama) ?>..."
                           class="message-input"
                           id="messageInput"
                           autocomplete="off">
                    
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-amber-600 hover:text-amber-700 transition-colors">
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
            input.style.boxShadow = '0 0 0 3px rgba(217, 119, 6, 0.3)';
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
            messageInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    document.querySelector('form').submit();
                }
            });
            
            // Auto-resize textarea (if needed)
            if (messageInput.tagName === 'TEXTAREA') {
                messageInput.addEventListener('input', function() {
                    this.style.height = 'auto';
                    this.style.height = (this.scrollHeight) + 'px';
                });
            }
        });
        
        // Submit form handler
        document.querySelector('form').addEventListener('submit', function(e) {
            // Add sending animation
            const sendBtn = this.querySelector('.send-btn');
            const originalHtml = sendBtn.innerHTML;
            sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
            
            setTimeout(() => {
                sendBtn.innerHTML = originalHtml;
            }, 1000);
            
            // Scroll to bottom after sending
            setTimeout(scrollToBottom, 100);
        });
        
        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('mobileOverlay');
            
            if (window.innerWidth < 768 && 
                sidebar.classList.contains('active') &&
                !sidebar.contains(e.target) &&
                !e.target.closest('.mobile-menu-btn')) {
                toggleSidebar();
            }
        });
        
        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K to focus input
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('messageInput').focus();
            }
            
            // Escape to clear input
            if (e.key === 'Escape') {
                document.getElementById('messageInput').value = '';
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