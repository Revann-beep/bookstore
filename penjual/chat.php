<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit;
}



$penjual_id   = $_SESSION['id_user'];
$penjual_nama = $_SESSION['nama'];

$chatWith  = (int)($_GET['user'] ?? 0);
$id_produk = (int)($_GET['id_produk'] ?? 0);

/* ================== KIRIM PESAN ================== */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $chatWith > 0 && $id_produk > 0) {
    $pesan = trim($_POST['pesan']);
    if ($pesan !== '') {
        mysqli_query($conn, "
            INSERT INTO messages (sender_id, receiver_id, id_produk, message, is_read, created_at)
            VALUES ('$penjual_id', '$chatWith', '$id_produk', '$pesan', 0, NOW())
        ");
    }
    header("Location: chat.php?user=$chatWith&id_produk=$id_produk");
    exit;
}

/* ================== NAMA PEMBELI ================== */
$pembeli_nama = '';
if ($chatWith > 0) {
    $q = mysqli_query ($conn, "SELECT nama FROM users WHERE id_user='$chatWith'");
    if ($r = mysqli_fetch_assoc($q)) {
        $pembeli_nama = $r['nama'];
    }
}

/* ================== MARK AS READ ================== */
if ($chatWith && $id_produk) {
    mysqli_query($conn, "
        UPDATE messages 
        SET is_read=1
        WHERE sender_id='$chatWith'
        AND receiver_id='$penjual_id'
        AND id_produk='$id_produk'
    ");
}

/* ================== CHAT HISTORY ================== */
// $chat = [];
// if ($chatWith && $id_produk) {
//     $qChat = mysqli_query($conn, "
//         SELECT * FROM messages
//         WHERE id_produk='$id_produk'
//         AND (
//             (sender_id='$penjual_id' AND receiver_id='$chatWith')
//             OR
//             (sender_id='$chatWith' AND receiver_id='$penjual_id')
//         )
//         ORDER BY created_at ASC
//     ");
//     while ($row = mysqli_fetch_assoc($qChat)) {
//         $chat[] = $row;
//     }
// }

$inboxQuery = mysqli_query($conn, "
    SELECT 
        u.id_user,
        u.nama,
        m.id_produk,
        m.message AS last_msg,
        m.created_at,
        (
            SELECT COUNT(*) 
            FROM messages 
            WHERE receiver_id = '$penjual_id'
              AND sender_id = u.id_user
              AND is_read = 0
        ) AS unread
    FROM messages m
    JOIN users u ON u.id_user = m.sender_id
    WHERE m.receiver_id = '$penjual_id'
    AND m.id_message IN (
        SELECT MAX(id_message)
        FROM messages
        WHERE receiver_id = '$penjual_id'
        GROUP BY sender_id, id_produk
    )
    ORDER BY m.created_at DESC
");

?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chat | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
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
            background-color: #eef2ff;
            border-left: 4px solid #4f46e5;
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
        .online-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            border: 2px solid white;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg flex flex-col fixed h-full">
        <!-- LOGO -->
        <div class="p-6 border-b">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                    <i class="fas fa-book text-white"></i>
                </div>
                <div>
                    <h2 class="font-bold text-gray-800">Aksara Jiwa</h2>
                    <p class="text-xs text-gray-500">Penjual Dashboard</p>
                </div>
            </div>
        </div>

        <!-- MENU -->
        <div class="flex-1 overflow-y-auto">
            <nav class="p-4 space-y-1">
                <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-chart-line w-5"></i> Dashboard
                </a>
                
                <a href="produk.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-box-open w-5"></i> Produk
                </a>
                <a href="approve.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-check-circle w-5"></i> Approve
                </a>
                <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-file-alt w-5"></i> Laporan
                </a>
                <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
                    <i class="fas fa-comments w-5"></i> Chat
                </a>
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-store w-5"></i> Data Penjual
                </a>
                <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-user-circle w-5"></i> Akun Saya
                </a>
                <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-question-circle w-5"></i> Bantuan
                </a>
            </nav>
        </div>

        <!-- LOGOUT -->
        <div class="p-4 border-t mt-auto">
            <a href="../auth/logout.php" class="flex items-center gap-3 text-red-500 hover:text-red-600">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <div class="ml-64 h-screen flex">
        <!-- LIST PEMBELI -->
        <div class="w-80 border-r bg-white shadow-sm">
            <div class="p-5 border-b">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Percakapan</h2>
                        <p class="text-sm text-gray-500 mt-1">Penjual: <?= htmlspecialchars($penjual_nama) ?></p>
                    </div>
                    <div class="relative">
                        <i class="fas fa-circle text-green-500 text-xs"></i>
                    </div>
                </div>
            </div>
            
            <div class="overflow-y-auto h-[calc(100vh-120px)] scrollbar-thin">
                <?php 
                if ($inboxQuery) {
    mysqli_data_seek($inboxQuery, 0);
}

                if (mysqli_num_rows($inboxQuery) > 0): 
                    while($row = mysqli_fetch_assoc($inboxQuery)): 
                        $last_msg = $row['last_msg'];
                        if (strlen($last_msg) > 40) {
                            $last_msg = substr($last_msg, 0, 40) . '...';
                        }
                ?>
                    <a href="?user=<?= $row['id_user'] ?>&id_produk=<?= $row['id_produk'] ?>" 
   class="flex items-center p-4 border-b hover:bg-gray-50 transition 
   <?= ($chatWith == $row['id_user'] && $id_produk == $row['id_produk']) ? 'active-chat' : '' ?>">

                        <div class="relative flex-shrink-0">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600"></i>
                            </div>
                            <?php if($row['unread'] > 0): ?>
                                <span class="absolute -top-1 -right-1 bg-red-500 text-white text-xs px-2 py-1 rounded-full min-w-[20px] text-center">
                                    <?= $row['unread'] ?>
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="flex-1 min-w-0 ml-3">
                            <div class="flex justify-between items-start">
                                <p class="font-semibold text-gray-900 truncate"><?= htmlspecialchars($row['nama']) ?></p>
                                <p class="text-xs text-gray-400 whitespace-nowrap ml-2"><?= date('H:i', strtotime($row['created_at'])) ?></p>
                            </div>
                            <p class="text-sm text-gray-600 truncate mt-1"><?= htmlspecialchars($last_msg) ?></p>
                        </div>
                    </a>
                <?php 
                    endwhile; 
                else: 
                ?>
                    <div class="p-8 text-center">
                        <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-comment-slash text-2xl text-gray-400"></i>
                        </div>
                        <p class="text-gray-500">Belum ada percakapan</p>
                        <p class="text-sm text-gray-400 mt-1">Pembeli akan muncul di sini</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- AREA CHAT -->
        <div class="flex-1 flex flex-col bg-white">
            <?php if($chatWith > 0): ?>

                
                <!-- HEADER CHAT -->
                <div class="p-5 border-b bg-white shadow-sm flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="relative">
                            <div class="w-12 h-12 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-full flex items-center justify-center">
                                <i class="fas fa-user text-indigo-600 text-lg"></i>
                            </div>
                            <span class="online-dot absolute bottom-0 right-0"></span>
                        </div>
                        <div>
                            <h3 class="font-bold text-gray-800"><?= htmlspecialchars($pembeli_nama) ?></h3>
                            <p class="text-sm text-gray-500">Sedang online</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <i class="fas fa-phone"></i>
                        </button>
                        <button class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <i class="fas fa-video"></i>
                        </button>
                        <button class="w-10 h-10 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 rounded-lg transition">
                            <i class="fas fa-ellipsis-v"></i>
                        </button>
                    </div>
                </div>

                <!-- CHAT MESSAGES -->
                <div id="chat-box"
     class="flex-1 p-6 overflow-y-auto chat-container scrollbar-thin space-y-4 bg-gray-50">
                    <?php if(empty($chat)): ?>
                        <div class="h-full flex flex-col items-center justify-center">
                            <div class="w-24 h-24 bg-gradient-to-r from-indigo-50 to-purple-50 rounded-full flex items-center justify-center mb-4">
                                <i class="fas fa-comments text-3xl text-indigo-500"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-700 mb-2">Mulai Percakapan</h3>
                            <p class="text-gray-500 text-center max-w-sm">
                                Kirim pesan pertama Anda kepada <?= htmlspecialchars($pembeli_nama) ?>
                            </p>
                        </div>
                    <?php else: ?>
                        <?php foreach($chat as $msg): ?>
                            <?php if($msg['sender_id'] == $penjual_id): ?>
                                <!-- PESAN PENJUAL -->
                                <div class="flex justify-end">
                                    <div class="max-w-lg">
                                        <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-4 message-right shadow">
                                            <?= htmlspecialchars($msg['message']) ?>
                                        </div>
                                        <div class="flex items-center justify-end gap-2 mt-1">
                                            <span class="text-xs text-gray-500"><?= date('H:i', strtotime($msg['created_at'])) ?></span>
                                            <?php if($msg['is_read']): ?>
                                                <i class="fas fa-check-double text-xs text-green-500"></i>
                                            <?php else: ?>
                                                <i class="fas fa-check text-xs text-gray-400"></i>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            <?php else: ?>
                                <!-- PESAN PEMBELI -->
                                <div class="flex justify-start">
                                    <div class="max-w-lg">
                                        <div class="bg-white p-4 message-left border shadow">
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
                <form method="POST"
      action="chat.php?user=<?= $chatWith ?>&id_produk=<?= $id_produk ?>"
      class="p-5 border-t bg-white shadow-sm">

                    <div class="flex gap-3">
                        <button type="button" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-paperclip text-xl"></i>
                        </button>
                        <button type="button" class="w-12 h-12 flex items-center justify-center text-gray-500 hover:text-indigo-600 hover:bg-gray-100 rounded-full">
                            <i class="fas fa-image text-xl"></i>
                        </button>
                        <input type="text" name="pesan" placeholder="Ketik pesan Anda..."
                               class="flex-1 border border-gray-300 rounded-full px-5 py-3 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"
                               required autofocus>
                        <button type="submit"
                                class="w-12 h-12 bg-gradient-to-r from-indigo-500 to-purple-600 hover:from-indigo-600 hover:to-purple-700 text-white rounded-full flex items-center justify-center">
                            <i class="fas fa-paper-plane text-lg"></i>
                        </button>
                    </div>
                </form>

            <?php else: ?>
                <!-- DEFAULT VIEW -->
                <div class="flex-1 flex flex-col items-center justify-center p-8 bg-gradient-to-br from-gray-50 to-indigo-50">
                    <div class="text-center max-w-lg">
                        <div class="w-32 h-32 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-full flex items-center justify-center mx-auto mb-6 shadow-lg">
                            <i class="fas fa-comments text-5xl text-indigo-500"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-800 mb-3">Chat dengan Pembeli</h3>
                        <p class="text-gray-600 mb-8 text-lg">
                            Pilih pembeli dari daftar di samping untuk memulai percakapan dan memberikan pelayanan terbaik.
                        </p>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                            <div class="bg-white p-4 rounded-xl shadow text-center">
                                <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-headset text-blue-600"></i>
                                </div>
                                <p class="font-medium text-gray-800">Dukungan Cepat</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow text-center">
                                <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-check-circle text-green-600"></i>
                                </div>
                                <p class="font-medium text-gray-800">Konfirmasi Pesanan</p>
                            </div>
                            <div class="bg-white p-4 rounded-xl shadow text-center">
                                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center mx-auto mb-3">
                                    <i class="fas fa-info-circle text-purple-600"></i>
                                </div>
                                <p class="font-medium text-gray-800">Informasi Produk</p>
                            </div>
                        </div>
                        <div class="text-sm text-gray-500">
                            <i class="fas fa-lightbulb text-yellow-500 mr-2"></i>
                            Respon yang cepat meningkatkan kepuasan pelanggan
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Auto scroll to bottom -->
    <script>
const chatBox = document.getElementById('chat-box');

if (!chatBox) {
    console.error('chat-box tidak ditemukan');
}

const penjualId = <?= (int)$penjual_id ?>;
const chatWith  = <?= (int)$chatWith ?>;
const idProduk  = <?= (int)$id_produk ?>;

function scrollBottom() {
    chatBox.scrollTop = chatBox.scrollHeight;
}

function loadChat() {
    fetch(`fetch_chat.php?user=${chatWith}&id_produk=${idProduk}`)
        .then(res => res.json())
        .then(data => {
            let html = '';
            data.forEach(msg => {
                if (msg.sender_id == penjualId) {
                    html += `
                    <div class="flex justify-end">
                        <div class="max-w-lg">
                            <div class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white p-4 message-right shadow">
                                ${msg.message}
                            </div>
                        </div>
                    </div>`;
                } else {
                    html += `
                    <div class="flex justify-start">
                        <div class="max-w-lg">
                            <div class="bg-white p-4 message-left border shadow">
                                ${msg.message}
                            </div>
                        </div>
                    </div>`;
                }
            });
            chatBox.innerHTML = html;
            scrollBottom();
        })
        .catch(err => console.error('Fetch error:', err));
}

// jalankan hanya kalau chat aktif
if (chatWith && idProduk) {
    loadChat();
    setInterval(loadChat, 2000);
}
</script>
</body>
</html>