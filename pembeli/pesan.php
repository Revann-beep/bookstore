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
    <title>Chat dengan Penjual - Sari Anggrek</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        
        :root {
            --primary-blue: #3b82f6;
            --primary-purple: #8b5cf6;
            --sidebar-width: 300px;
            --chat-header-height: 80px;
            --input-height: 80px;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: #f5f7fa;
            height: 100vh;
            overflow: hidden;
        }
        
        /* HORIZONTAL LAYOUT */
        .main-container {
            display: flex;
            height: 100vh;
            overflow: hidden;
        }
        
        /* SIDEBAR LEFT */
        .sidebar-left {
            width: var(--sidebar-width);
            background: linear-gradient(180deg, #ffffff 0%, #fafbfd 100%);
            border-right: 1px solid #e5e7eb;
            display: flex;
            flex-direction: column;
            box-shadow: 2px 0 10px rgba(0, 0, 0, 0.05);
            z-index: 40;
        }
        
        /* CHAT AREA RIGHT */
        .chat-area {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0; /* Untuk mencegah overflow */
        }
        
        /* CHAT MESSAGES */
        .chat-messages-container {
            flex: 1;
            overflow-y: auto;
            background: #f8fafc;
            background-image: 
                radial-gradient(#e2e8f0 1px, transparent 1px),
                radial-gradient(#e2e8f0 1px, transparent 1px);
            background-size: 40px 40px;
            background-position: 0 0, 20px 20px;
            background-attachment: fixed;
        }
        
        /* MESSAGE STYLES */
        .message-seller {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            border-radius: 18px 18px 18px 4px;
            max-width: 70%;
            margin-right: auto;
            position: relative;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }
        
        .message-seller::before {
            content: '';
            position: absolute;
            left: -8px;
            top: 10px;
            width: 0;
            height: 0;
            border: 8px solid transparent;
            border-right-color: #ffffff;
            border-left: 0;
        }
        
        .message-buyer {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border-radius: 18px 18px 4px 18px;
            max-width: 70%;
            margin-left: auto;
            position: relative;
            box-shadow: 0 2px 10px rgba(59, 130, 246, 0.2);
        }
        
        .message-buyer::before {
            content: '';
            position: absolute;
            right: -8px;
            top: 10px;
            width: 0;
            height: 0;
            border: 8px solid transparent;
            border-left-color: #3b82f6;
            border-right: 0;
        }
        
        /* HEADERS */
        .chat-header {
            height: var(--chat-header-height);
            background: #ffffff;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            padding: 0 24px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .input-container {
            height: var(--input-height);
            background: #ffffff;
            border-top: 1px solid #e5e7eb;
            display: flex;
            align-items: center;
            padding: 0 24px;
        }
        
        /* SCROLLBAR */
        .scrollbar-thin::-webkit-scrollbar {
            width: 6px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-track {
            background: rgba(0, 0, 0, 0.05);
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: rgba(59, 130, 246, 0.3);
            border-radius: 3px;
        }
        
        .scrollbar-thin::-webkit-scrollbar-thumb:hover {
            background: rgba(59, 130, 246, 0.5);
        }
        
        /* ONLINE INDICATOR */
        .online-indicator {
            width: 8px;
            height: 8px;
            background: #10b981;
            border-radius: 50%;
            position: absolute;
            bottom: 0;
            right: 0;
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
            background: #e5e7eb;
            z-index: 1;
        }
        
        .date-label {
            display: inline-block;
            background: #ffffff;
            padding: 4px 16px;
            border-radius: 20px;
            font-size: 12px;
            color: #6b7280;
            border: 1px solid #e5e7eb;
            position: relative;
            z-index: 2;
        }
        
        /* QUICK ACTIONS */
        .quick-action-btn {
            background: #ffffff;
            border: 1px solid #e5e7eb;
            color: #4b5563;
            padding: 8px 16px;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.2s;
            text-align: left;
            width: 100%;
        }
        
        .quick-action-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            transform: translateY(-1px);
        }
        
        /* QUICK REPLIES */
        .quick-reply-btn {
            background: #ffffff;
            border: 1px solid #dbeafe;
            color: #1e40af;
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        
        .quick-reply-btn:hover {
            background: #dbeafe;
            transform: translateY(-1px);
        }
        
        /* NAVIGATION */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            color: #4b5563;
            border-radius: 8px;
            transition: all 0.2s;
        }
        
        .nav-item:hover {
            background: #f3f4f6;
            color: #1f2937;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.2);
        }
        
        /* INPUT FIELD */
        .message-input {
            flex: 1;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 24px;
            padding: 12px 20px;
            font-size: 14px;
            transition: all 0.2s;
        }
        
        .message-input:focus {
            outline: none;
            border-color: #3b82f6;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
        }
        
        /* SEND BUTTON */
        .send-btn {
            width: 44px;
            height: 44px;
            background: linear-gradient(135deg, #3b82f6 0%, #8b5cf6 100%);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
            margin-left: 12px;
        }
        
        .send-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.3);
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
                width: 280px;
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
                background: rgba(0, 0, 0, 0.5);
                z-index: 30;
            }
            
            .mobile-overlay.active {
                display: block;
            }
            
            .mobile-menu-btn {
                display: block;
            }
        }
        
        /* ANIMATIONS */
        .message-animation {
            animation: messageAppear 0.3s ease-out;
        }
        
        @keyframes messageAppear {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        /* BADGE */
        .unread-badge {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            font-size: 11px;
            padding: 2px 8px;
            border-radius: 10px;
            animation: bounce 2s infinite;
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
    </style>
</head>
<body>
    <div class="main-container">
        
        <!-- SIDEBAR LEFT -->
        <div class="sidebar-left" id="sidebar">
            <!-- Header -->
            <div class="p-6 border-b">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-xl flex items-center justify-center">
                        <i class="fas fa-book text-white text-lg"></i>
                    </div>
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">SARI ANGGREK</h1>
                        <p class="text-xs text-gray-500">Online Bookstore</p>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="bg-gradient-to-r from-blue-50 to-purple-50 p-4 rounded-xl mb-6">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-white"></i>
                        </div>
                        <div>
                            <p class="font-medium text-gray-900"><?= htmlspecialchars($pembeli_nama) ?></p>
                            <p class="text-xs text-gray-600">Pembeli</p>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation -->
            <div class="flex-1 p-4">
                <nav class="space-y-2">
                    <a href="dashboard_pembeli.php" class="nav-item">
                        <i class="fas fa-home w-5 text-center"></i>
                        <span>Dashboard</span>
                    </a>
                    
                    <a href="halaman-pesanan.php" class="nav-item">
                        <i class="fas fa-shopping-cart w-5 text-center"></i>
                        <span>Pesanan</span>
                    </a>
                    
                    <a href="status.php" class="nav-item">
                        <i class="fas fa-truck w-5 text-center"></i>
                        <span>Status</span>
                    </a>
                    
                    <a href="pesan.php" class="nav-item active">
                        <i class="fas fa-comments w-5 text-center"></i>
                        <span>Chat</span>
                        <?php if($unreadCount > 0): ?>
                            <span class="unread-badge ml-auto"><?= $unreadCount ?></span>
                        <?php endif; ?>
                    </a>
                    
                    <a href="my.php" class="nav-item">
                        <i class="fas fa-user-cog w-5 text-center"></i>
                        <span>My Account</span>
                    </a>
                </nav>
                
                <!-- Quick Actions -->
                <div class="mt-8">
                    <h3 class="text-sm font-semibold text-gray-700 mb-3 px-4">Quick Actions</h3>
                    <div class="space-y-2 px-4">
                        <button onclick="quickMessage('Apakah buku ini ready stock?')" 
                                class="quick-action-btn">
                            <i class="fas fa-box text-blue-500 mr-2"></i>
                            Tanya ketersediaan
                        </button>
                        
                        <button onclick="quickMessage('Berapa estimasi pengiriman?')" 
                                class="quick-action-btn">
                            <i class="fas fa-clock text-purple-500 mr-2"></i>
                            Tanya estimasi
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="p-4 border-t">
                <a href="../auth/logout.php" class="nav-item text-red-600">
                    <i class="fas fa-sign-out-alt w-5 text-center"></i>
                    <span>Sign Out</span>
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
                <button class="mobile-menu-btn md:hidden mr-4 text-gray-600" onclick="toggleSidebar()">
                    <i class="fas fa-bars text-xl"></i>
                </button>
                
                <!-- Seller Info -->
                <div class="flex items-center gap-3 flex-1">
                    <div class="relative">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                            <i class="fas fa-store text-white"></i>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div>
                        <h2 class="font-bold text-gray-900"><?= htmlspecialchars($penjual_nama) ?></h2>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-gray-500">Penjual Toko Buku</span>
                            <span class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full">Online</span>
                        </div>
                    </div>
                </div>
                
                <!-- Buyer Info (Desktop) -->
                <div class="hidden md:flex items-center gap-3">
                    <div class="text-right">
                        <p class="font-medium text-gray-900"><?= htmlspecialchars($pembeli_nama) ?></p>
                        <p class="text-xs text-gray-500">Pembeli</p>
                    </div>
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                        <i class="fas fa-user text-white"></i>
                    </div>
                </div>
            </div>
            
            <!-- Quick Replies -->
            <div class="bg-white border-b py-3 px-6">
                <div class="flex gap-2 overflow-x-auto pb-2">
                    <button onclick="quickMessage('Halo, apakah ada yang bisa dibantu?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-hand-wave mr-2"></i>Halo
                    </button>
                    
                    <button onclick="quickMessage('Apakah One Piece tersedia?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-book mr-2"></i>Stok One Piece
                    </button>
                    
                    <button onclick="quickMessage('Berapa harga buku One Piece?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-tag mr-2"></i>Tanya Harga
                    </button>
                    
                    <button onclick="quickMessage('Apakah bisa COD?')" 
                            class="quick-reply-btn">
                        <i class="fas fa-money-bill-wave mr-2"></i>Metode Bayar
                    </button>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div class="chat-messages-container scrollbar-thin p-6">
                
                <?php if(empty($chatHistory)): ?>
                    <!-- Empty State -->
                    <div class="empty-state">
                        <div class="w-20 h-20 bg-gradient-to-br from-blue-100 to-purple-100 rounded-full flex items-center justify-center mb-4">
                            <i class="fas fa-comments text-3xl text-blue-500"></i>
                        </div>
                        <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada percakapan</h3>
                        <p class="text-gray-600 text-center max-w-sm mb-6">
                            Mulai percakapan dengan <?= htmlspecialchars($penjual_nama) ?> untuk menanyakan ketersediaan produk atau informasi lainnya.
                        </p>
                        <button onclick="quickMessage('Halo, apakah bisa membantu saya?')" 
                                class="bg-gradient-to-r from-blue-500 to-purple-600 text-white px-6 py-3 rounded-full font-medium hover:shadow-lg transition-all">
                            <i class="fas fa-comment-medical mr-2"></i>Mulai Percakapan
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
                            <span class="date-label"><?= $displayDate ?></span>
                        </div>
                    <?php 
                        endif;
                        $lastDate = $messageDate;
                    ?>
                    
                    <!-- Message -->
                    <div class="message-animation mb-4">
                        <?php if ($m['sender_id'] == $pembeli_id): ?>
                            <!-- Buyer Message (Right) -->
                            <div class="flex justify-end">
                                <div class="message-buyer px-4 py-3">
                                    <div class="text-white text-sm">
                                        <?= htmlspecialchars($m['message']) ?>
                                    </div>
                                    <div class="flex items-center justify-end gap-2 mt-2">
                                        <span class="text-xs text-blue-100 opacity-80">
                                            <?= date('H:i', strtotime($m['created_at'])) ?>
                                        </span>
                                        <span class="text-xs text-blue-100 opacity-80">
                                            <i class="fas fa-check-double"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        <?php else: ?>
                            <!-- Seller Message (Left) -->
                            <div class="flex">
                                <div class="message-seller px-4 py-3">
                                    <div class="text-gray-800 text-sm">
                                        <?= htmlspecialchars($m['message']) ?>
                                    </div>
                                    <div class="text-xs text-gray-400 mt-2">
                                        <?= date('H:i', strtotime($m['created_at'])) ?>
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
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-blue-500 transition-colors">
                        <i class="fas fa-plus text-lg"></i>
                    </button>
                    
                    <input type="text" 
                           name="pesan" 
                           required
                           placeholder="Ketik pesan untuk <?= htmlspecialchars($penjual_nama) ?>..."
                           class="message-input"
                           id="messageInput"
                           autocomplete="off">
                    
                    <button type="button" class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-yellow-500 transition-colors">
                        <i class="far fa-smile text-lg"></i>
                    </button>
                    
                    <button type="submit" class="send-btn">
                        <i class="fas fa-paper-plane"></i>
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
            
            // Prevent body scroll when sidebar is open
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
            
            // Add animation effect
            input.style.transform = 'scale(1.02)';
            setTimeout(() => {
                input.style.transform = '';
            }, 200);
        }
        
        // Auto scroll to bottom
        function scrollToBottom() {
            const container = document.querySelector('.chat-messages-container');
            if (container) {
                container.scrollTop = container.scrollHeight;
            }
        }
        
        // Auto refresh chat every 5 seconds
        setInterval(() => {
            fetch('check_new_messages.php')
                .then(response => response.json())
                .then(data => {
                    if (data.has_new_messages) {
                        location.reload();
                    }
                })
                .catch(error => console.error('Error:', error));
        }, 5000);
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to bottom
            scrollToBottom();
            
            // Add animation to existing messages
            const messages = document.querySelectorAll('.message-animation');
            messages.forEach((msg, index) => {
                setTimeout(() => {
                    msg.style.opacity = '1';
                    msg.style.transform = 'translateY(0)';
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
            // Optional: Add sending animation
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
    </script>
</body>
</html>