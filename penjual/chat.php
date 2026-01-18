<?php
session_start();
require '../auth/connection.php';

// CEK LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// HANYA PENJUAL YANG BOLEH CHAT
if ($_SESSION['role'] !== 'penjual') {
    die("Akses ditolak");
}

$penjual_id = $_SESSION['id_user'];
$penjual_nama = $_SESSION['nama'];

// AMBIL DAFTAR PEMBELI YANG PERNAH CHAT
$inboxQuery = mysqli_query($conn, "
    SELECT 
        u.id_user, 
        u.nama,
        m.message as last_msg,
        m.created_at as last_time,
        (SELECT COUNT(*) FROM messages WHERE receiver_id = '$penjual_id' AND sender_id = u.id_user AND is_read = 0) as unread
    FROM messages m
    JOIN users u ON u.id_user = m.sender_id
    WHERE m.receiver_id = '$penjual_id' AND m.id_message IN (
        SELECT MAX(id_message) FROM messages WHERE receiver_id = '$penjual_id' GROUP BY sender_id
    )
    GROUP BY u.id_user
    ORDER BY m.created_at DESC
");

// JIKA PEMBELI DIPILIH
$chatWith = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// MARK AS READ
if ($chatWith > 0) {
    mysqli_query($conn, "UPDATE messages SET is_read = 1 WHERE sender_id = '$chatWith' AND receiver_id = '$penjual_id'");
}

// KIRIM PESAN
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['pesan']) && $chatWith > 0) {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);
    mysqli_query($conn, "
        INSERT INTO messages (sender_id, receiver_id, message)
        VALUES ('$penjual_id', '$chatWith', '$pesan')
    ");
    
    // Redirect untuk refresh chat
    header("Location: chat.php?user=$chatWith");
    exit;
}

// AMBIL CHAT JIKA ADA PEMBELI DIPILIH
$chat = [];
$pembeli_nama = '';
if ($chatWith > 0) {
    $userQuery = mysqli_query($conn, "SELECT nama FROM users WHERE id_user = '$chatWith'");
    $userData = mysqli_fetch_assoc($userQuery);
    $pembeli_nama = $userData['nama'] ?? '';
    
    $chatQuery = mysqli_query($conn, "
        SELECT m.*, u.nama as sender_nama 
        FROM messages m
        JOIN users u ON u.id_user = m.sender_id
        WHERE (sender_id='$penjual_id' AND receiver_id='$chatWith')
           OR (sender_id='$chatWith' AND receiver_id='$penjual_id')
        ORDER BY created_at ASC
    ");
    
    while ($row = mysqli_fetch_assoc($chatQuery)) {
        $chat[] = $row;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat dengan Pembeli</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .chat-container {
            height: calc(100vh - 140px);
        }
        .message-left {
            border-radius: 18px 18px 18px 4px;
        }
        .message-right {
            border-radius: 18px 18px 4px 18px;
        }
        .active-chat {
            background-color: #f0fdfa;
            border-left: 4px solid #0d9488;
        }
        .scrollbar-thin::-webkit-scrollbar {
            width: 4px;
        }
        .scrollbar-thin::-webkit-scrollbar-track {
            background: #f1f1f1;
        }
        .scrollbar-thin::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 2px;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- SIDEBAR -->
    <div class="fixed left-0 top-0 h-screen w-64 bg-white shadow-lg">
        <div class="p-6 border-b">
            <h1 class="text-xl font-bold text-teal-700">📚 Toko Buku</h1>
            <p class="text-sm text-gray-500 mt-1">Penjual: <?= htmlspecialchars($penjual_nama) ?></p>
        </div>

        <nav class="p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                <i class="fas fa-chart-line w-5"></i> Dashboard
            </a>
            <a href="produk.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                <i class="fas fa-box w-5"></i> Produk
            </a>
            
            <div class="my-2">
                <p class="text-xs font-semibold text-gray-400 px-3 mb-2">ORDER</p>
                <a href="approve.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-check-circle w-5"></i> Approve
                </a>
                <a href="laporan.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-file-alt w-5"></i> Laporan
                </a>
                <a href="chat.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg active-chat">
                    <i class="fas fa-comments w-5"></i> Chat
                </a>
            </div>
            
            <div class="mt-4">
                <a href="akun_saya.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-gray-700 hover:bg-teal-50">
                    <i class="fas fa-user-circle w-5"></i> My Account
                </a>
                <a href="../auth/logout.php" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-red-500 hover:bg-red-50">
                    <i class="fas fa-sign-out-alt w-5"></i> Sign Out
                </a>
            </div>
        </nav>
    </div>

    <!-- MAIN CONTENT -->
    <div class="ml-64 h-screen flex">
        <!-- LIST PEMBELI -->
        <div class="w-80 border-r bg-white">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold text-gray-800">💬 Chat Pembeli</h2>
                <p class="text-sm text-gray-500 mt-1">Pilih pembeli untuk memulai chat</p>
            </div>
            
            <div class="overflow-y-auto h-[calc(100vh-120px)] scrollbar-thin">
                <?php 
                mysqli_data_seek($inboxQuery, 0); // Reset pointer
                if (mysqli_num_rows($inboxQuery) > 0): 
                    while($row = mysqli_fetch_assoc($inboxQuery)): 
                        $last_msg = $row['last_msg'];
                        if (strlen($last_msg) > 40) {
                            $last_msg = substr($last_msg, 0, 40) . '...';
                        }
                ?>
                    <a href="?user=<?= $row['id_user'] ?>" 
                       class="flex items-center p-3 border-b hover:bg-gray-50 <?= $chatWith == $row['id_user'] ? 'bg-teal-50' : '' ?>">
                        <div class="flex-1 min-w-0">
                            <div class="flex justify-between items-start">
                                <p class="font-medium text-gray-900 truncate"><?= htmlspecialchars($row['nama']) ?></p>
                                <?php if($row['unread'] > 0): ?>
                                    <span class="bg-red-500 text-white text-xs px-2 py-1 rounded-full"><?= $row['unread'] ?></span>
                                <?php endif; ?>
                            </div>
                            <p class="text-sm text-gray-500 truncate mt-1"><?= htmlspecialchars($last_msg) ?></p>
                            <p class="text-xs text-gray-400 mt-1"><?= date('H:i', strtotime($row['last_time'])) ?></p>
                        </div>
                    </a>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="p-8 text-center">
                        <i class="fas fa-comment-slash text-3xl text-gray-300 mb-3"></i>
                        <p class="text-gray-500">Belum ada percakapan</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AREA CHAT -->
        <div class="flex-1 flex flex-col">
            <?php if($chatWith > 0 && !empty($pembeli_nama)): ?>
                <!-- HEADER CHAT -->
                <div class="p-4 border-b bg-white flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center">
                            <i class="fas fa-user text-teal-600"></i>
                        </div>
                        <div>
                            <h3 class="font-semibold text-gray-800"><?= htmlspecialchars($pembeli_nama) ?></h3>
                            <p class="text-xs text-gray-500">Sedang online</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="p-2 text-gray-500 hover:text-teal-600 hover:bg-teal-50 rounded-lg">
                            <i class="fas fa-video"></i>
                        </button>
                    </div>
                </div>

                <!-- CHAT MESSAGES -->
                <div class="flex-1 p-4 overflow-y-auto chat-container scrollbar-thin space-y-4 bg-gray-50">
                    <?php if(empty($chat)): ?>
                        <div class="h-full flex flex-col items-center justify-center">
                            <i class="fas fa-comments text-4xl text-gray-300 mb-4"></i>
                            <p class="text-gray-500">Mulai percakapan dengan <?= htmlspecialchars($pembeli_nama) ?></p>
                        </div>
                    <?php else: ?>
                        <?php foreach($chat as $msg): ?>
                            <?php if($msg['sender_id'] == $penjual_id): ?>
                                <!-- PESAN PENJUAL -->
                                <div class="flex justify-end">
                                    <div class="max-w-md">
                                        <div class="bg-teal-500 text-white p-4 message-right shadow-sm">
                                            <?= htmlspecialchars($msg['message']) ?>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1 text-right"><?= date('H:i', strtotime($msg['created_at'])) ?></p>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- PESAN PEMBELI -->
                                <div class="flex justify-start">
                                    <div class="max-w-md">
                                        <div class="bg-white p-4 message-left border shadow-sm">
                                            <?= htmlspecialchars($msg['message']) ?>
                                        </div>
                                        <p class="text-xs text-gray-500 mt-1"><?= date('H:i', strtotime($msg['created_at'])) ?></p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <!-- INPUT CHAT -->
                <form method="POST" class="p-4 border-t bg-white">
                    <div class="flex gap-3">
                        <input type="text" name="pesan" placeholder="Ketik pesan..."
                               class="flex-1 border border-gray-300 rounded-full px-6 py-3 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent"
                               required autofocus>
                        <button type="submit"
                                class="bg-teal-600 hover:bg-teal-700 text-white px-6 py-3 rounded-full font-medium">
                            <i class="fas fa-paper-plane mr-2"></i> Kirim
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <!-- DEFAULT VIEW -->
                <div class="flex-1 flex flex-col items-center justify-center p-8">
                    <div class="text-center max-w-md">
                        <div class="w-24 h-24 bg-teal-100 rounded-full flex items-center justify-center mx-auto mb-6">
                            <i class="fas fa-comments text-4xl text-teal-500"></i>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-800 mb-2">Chat dengan Pembeli</h3>
                        <p class="text-gray-500 mb-6">Pilih pembeli dari daftar di samping untuk memulai percakapan</p>
                        <div class="space-y-3 text-sm text-gray-600">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                <span>Jawab pertanyaan pembeli dengan cepat</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                <span>Konfirmasi pesanan melalui chat</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-teal-500"></i>
                                <span>Berikan informasi produk</span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Auto scroll to bottom -->
    <script>
        function scrollToBottom() {
            const chatContainer = document.querySelector('.chat-container');
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        }
        
        // Scroll saat halaman dimuat
        document.addEventListener('DOMContentLoaded', scrollToBottom);
        
        // Auto refresh chat setiap 5 detik
        setInterval(() => {
            if (<?= $chatWith ?>) {
                location.reload();
            }
        }, 5000);
    </script>
</body>
</html>