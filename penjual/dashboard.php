<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_penjual = $_SESSION['id_user'];

// AMBIL DATA PENJUAL
$user = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_penjual'");
$user = mysqli_fetch_assoc($user);

// AMBIL NOTIFIKASI CHAT BARU
$notifikasi_chat = mysqli_query($conn, "
    SELECT COUNT(*) as total_unread 
    FROM messages 
    WHERE receiver_id = '$id_penjual' 
    AND is_read = 0
");
$chat_notif = mysqli_fetch_assoc($notifikasi_chat);
$total_unread = $chat_notif['total_unread'] ?? 0;

// AMBIL 3 PESAN TERBARU UNTUK DROPDOWN
$recent_chats = mysqli_query($conn, "
    SELECT m.*, u.nama as sender_nama
    FROM messages m
    JOIN users u ON u.id_user = m.sender_id
    WHERE m.receiver_id = '$id_penjual'
    ORDER BY m.created_at DESC
    LIMIT 3
");

// ambil kategori
$kategori = mysqli_query($conn, "SELECT * FROM kategori ORDER BY id_kategori DESC");

// tanggal real time
$tanggal = date('d');
$bulan   = date('F Y');
$hari    = date('l');

// ubah hari ke indonesia
$hari_indo = [
  'Sunday' => 'Minggu',
  'Monday' => 'Senin',
  'Tuesday' => 'Selasa',
  'Wednesday' => 'Rabu',
  'Thursday' => 'Kamis',
  'Friday' => 'Jumat',
  'Saturday' => 'Sabtu'
];
$hari = $hari_indo[$hari];
?>


<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Penjual</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    .notification-badge {
      position: absolute;
      top: -5px;
      right: -5px;
      width: 18px;
      height: 18px;
      border-radius: 50%;
      background-color: #ef4444;
      color: white;
      font-size: 10px;
      display: flex;
      align-items: center;
      justify-content: center;
      font-weight: bold;
    }
    .dropdown-notif {
      animation: slideDown 0.2s ease-out;
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateY(-10px);
      }
      to {
        opacity: 1;
        transform: translateY(0);
      }
    }
    .pulse {
      animation: pulse 2s infinite;
    }
    @keyframes pulse {
      0% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.7);
      }
      70% {
        box-shadow: 0 0 0 10px rgba(239, 68, 68, 0);
      }
      100% {
        box-shadow: 0 0 0 0 rgba(239, 68, 68, 0);
      }
    }
  </style>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

  <!-- SIDEBAR -->
  <aside class="w-64 bg-white shadow-lg flex flex-col h-screen">

  <!-- LOGO -->
  <div class="p-6 flex items-center gap-2 border-b">
    <div class="w-10 h-10 bg-teal-500 text-white rounded-full flex items-center justify-center font-bold">
      <i class="fas fa-book"></i>
    </div>
    <span class="font-bold text-teal-600">SARI ANGREK</span>
  </div>

  <!-- MENU -->
  <div class="flex-1 px-4 py-6 space-y-2 text-sm">

    <!-- Dashboard -->
    <a href="dashboard.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      <i class="fas fa-chart-line w-5"></i> Dashboard
    </a>

    <!-- Produk -->
    <a href="produk.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      <i class="fas fa-box w-5"></i> Produk
    </a>

    <!-- Approve -->
   <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleApprove()"
              class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-teal-100">
        <span class="flex items-center gap-3">
          <i class="fas fa-check-circle w-5"></i> Approve
        </span>
        <span id="iconApprove">▼</span>
      </button>

      <div id="approveMenu" class="hidden px-4 pb-2 space-y-2">
        <a href="approve.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-2">
          <i class="fas fa-check w-4"></i> Approve
        </a>
        <a href="laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-2">
          <i class="fas fa-file-alt w-4"></i> Laporan
        </a>
        <a href="chat.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 flex items-center gap-2">
          <i class="fas fa-comments w-4"></i> Chat
        </a>
      </div>
    </div>

    <!-- My Account -->
    <a href="akun_saya.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      <i class="fas fa-user-circle w-5"></i> My Account
    </a>

    <!-- Sign Out -->
    <a href="../auth/logout.php" onclick="logoutConfirm(); return false;"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-red-50 text-red-500">
      <i class="fas fa-sign-out-alt w-5"></i> Sign Out
    </a>

  </div>

  <!-- HELP (paling bawah) -->
  <div class="px-4 py-4 border-t">
    <a href="help.php"
       class="flex items-center gap-3 text-gray-500 hover:text-teal-600">
      <i class="fas fa-question-circle w-5"></i> Help
    </a>
  </div>

</aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 p-6">

    <!-- TOP BAR -->
    <div class="flex justify-between items-center mb-6">
      <div class="flex items-center bg-white px-4 py-2 rounded-full shadow w-96">
        <i class="fas fa-search text-gray-400"></i>
        <input type="text" placeholder="Search Here"
          class="ml-2 w-full outline-none text-sm bg-transparent">
      </div>

      <div class="flex items-center gap-4">
        <!-- Notification Bell -->
        <div class="relative">
          <button id="notificationBtn" 
                  class="bg-teal-500 hover:bg-teal-600 text-white p-3 rounded-full relative <?= $total_unread > 0 ? 'pulse' : '' ?>">
            <i class="fas fa-bell"></i>
            <?php if ($total_unread > 0): ?>
              <span class="notification-badge"><?= $total_unread > 99 ? '99+' : $total_unread ?></span>
            <?php endif; ?>
          </button>
          
          <!-- Notification Dropdown -->
          <div id="notificationDropdown" 
               class="hidden absolute right-0 mt-2 w-80 bg-white rounded-lg shadow-xl border z-50 dropdown-notif">
            <div class="p-4 border-b">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                <?php if ($total_unread > 0): ?>
                  <span class="text-xs text-teal-600 font-medium">
                    <?= $total_unread ?> pesan baru
                  </span>
                <?php endif; ?>
              </div>
            </div>
            
            <div class="max-h-96 overflow-y-auto">
              <?php if (mysqli_num_rows($recent_chats) > 0): ?>
                <?php while($chat = mysqli_fetch_assoc($recent_chats)): 
                  $message = $chat['message'];
                  if (strlen($message) > 50) {
                    $message = substr($message, 0, 50) . '...';
                  }
                  $time = date('H:i', strtotime($chat['created_at']));
                ?>
                  <a href="chat.php?user=<?= $chat['sender_id'] ?>" 
                     class="block p-4 border-b hover:bg-gray-50">
                    <div class="flex items-start gap-3">
                      <div class="w-10 h-10 bg-teal-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-teal-600"></i>
                      </div>
                      <div class="flex-1">
                        <div class="flex justify-between">
                          <h4 class="font-medium text-gray-900"><?= htmlspecialchars($chat['sender_nama']) ?></h4>
                          <span class="text-xs text-gray-500"><?= $time ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1"><?= htmlspecialchars($message) ?></p>
                        <?php if(!$chat['is_read']): ?>
                          <span class="inline-block w-2 h-2 bg-red-500 rounded-full mt-2"></span>
                        <?php endif; ?>
                      </div>
                    </div>
                  </a>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="p-8 text-center">
                  <i class="fas fa-bell-slash text-3xl text-gray-300 mb-3"></i>
                  <p class="text-gray-500">Tidak ada notifikasi</p>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="p-4 border-t">
              <a href="chat.php" 
                 class="block text-center text-teal-600 hover:text-teal-800 font-medium">
                Lihat semua pesan →
              </a>
            </div>
          </div>
        </div>

        <!-- Settings -->
        <button class="bg-teal-500 hover:bg-teal-600 text-white p-3 rounded-full">
          <i class="fas fa-cog"></i>
        </button>

        <!-- User Profile -->
        <div class="flex items-center gap-2 bg-white px-4 py-2 rounded-full shadow">
          <span class="text-sm font-semibold">
            <?= htmlspecialchars($user['nama']) ?>
          </span>
          <img src="../img/<?= $user['image'] ?: 'default.png' ?>"
               class="w-8 h-8 rounded-full object-cover"
               onerror="this.src='../img/default.png'"
               alt="Profile">
        </div>
      </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

      <!-- CENTER -->
      <div class="xl:col-span-3 space-y-6">
        <div class="bg-gradient-to-r from-teal-400 to-teal-600 rounded-2xl p-8 text-white flex justify-between shadow-lg">
          <div>
            <h2 class="text-2xl font-bold mb-2">Sari Anggrek</h2>
            <p class="mb-4">
              Selalu di depan<br>
              Melayani kebutuhan anda
            </p>
          </div>
          <div class="self-end">
            <?php if ($total_unread > 0): ?>
              <div class="bg-white text-teal-600 px-4 py-2 rounded-lg">
                <i class="fas fa-comment-dots mr-2"></i>
                <?= $total_unread ?> pesan belum dibaca
              </div>
            <?php endif; ?>
          </div>
        </div>

        <!-- YOUR BOOK -->
        <div>
          <div class="flex justify-between items-center mb-4">
            <h3 class="font-semibold text-gray-700 text-lg">Kategori Buku</h3>
            <a href="#" class="text-teal-500 text-sm font-semibold hover:text-teal-700">Lihat Semua</a>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <?php 
            mysqli_data_seek($kategori, 0); // Reset pointer
            while ($row = mysqli_fetch_assoc($kategori)) : 
            ?>
              <div class="bg-white p-4 rounded-2xl shadow flex flex-col items-center hover:shadow-lg transition hover:-translate-y-1">
                <div class="w-12 h-12 bg-teal-100 rounded-full flex items-center justify-center mb-3">
                  <i class="fas fa-book text-teal-600"></i>
                </div>
                <span class="mt-2 text-sm font-semibold text-center">
                  <?= htmlspecialchars($row['nama_kategori']) ?>
                </span>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="space-y-6">
        <div class="bg-gradient-to-b from-teal-400 to-teal-600 rounded-2xl p-6 text-white shadow">
          <h4 class="text-sm font-semibold mb-3">Kalender</h4>
          <div class="text-center">
            <p class="text-sm"><?= $bulan ?></p>
            <p class="text-4xl font-bold my-2"><?= $tanggal ?></p>
            <p class="text-sm"><?= $hari ?></p>
          </div>
        </div>

        <!-- Quick Stats -->
        <div class="bg-white rounded-2xl p-6 shadow">
          <h4 class="font-semibold text-gray-800 mb-4">Statistik Hari Ini</h4>
          <div class="space-y-3">
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Total Pesanan</span>
              <span class="font-semibold">12</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Pendapatan</span>
              <span class="font-semibold text-green-600">Rp 1.250.000</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Pembeli Aktif</span>
              <span class="font-semibold">8</span>
            </div>
            <div class="flex justify-between items-center">
              <span class="text-sm text-gray-600">Pesan Baru</span>
              <span class="font-semibold <?= $total_unread > 0 ? 'text-red-600' : 'text-gray-600' ?>">
                <?= $total_unread ?>
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
function toggleApprove() {
  const menu = document.getElementById('approveMenu');
  const icon = document.getElementById('iconApprove');
  menu.classList.toggle('hidden');
  icon.textContent = menu.classList.contains('hidden') ? '▼' : '▲';
}

function logoutConfirm(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Yakin ingin logout?',
    text: "Anda akan keluar dari sistem",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#14b8a6',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, Logout',
    cancelButtonText: 'Batal'
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "../auth/logout.php";
    }
  });
}

// Notification dropdown toggle
document.getElementById('notificationBtn').addEventListener('click', function(e) {
  e.stopPropagation();
  const dropdown = document.getElementById('notificationDropdown');
  dropdown.classList.toggle('hidden');
  
  // Mark as read via AJAX
  if (!dropdown.classList.contains('hidden') && <?= $total_unread ?> > 0) {
    fetch('mark_as_read.php')
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Remove badge and pulse animation
          const badge = document.querySelector('.notification-badge');
          if (badge) badge.remove();
          this.classList.remove('pulse');
        }
      });
  }
});

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
  const dropdown = document.getElementById('notificationDropdown');
  const btn = document.getElementById('notificationBtn');
  
  if (!dropdown.contains(e.target) && !btn.contains(e.target)) {
    dropdown.classList.add('hidden');
  }
});

// Auto refresh notification every 30 seconds
setInterval(() => {
  fetch('get_notification_count.php')
    .then(response => response.json())
    .then(data => {
      if (data.unread_count > 0) {
        // Update badge
        const badge = document.querySelector('.notification-badge');
        if (badge) {
          badge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
        } else {
          // Create badge if not exists
          const btn = document.getElementById('notificationBtn');
          const newBadge = document.createElement('span');
          newBadge.className = 'notification-badge';
          newBadge.textContent = data.unread_count > 99 ? '99+' : data.unread_count;
          btn.appendChild(newBadge);
          btn.classList.add('pulse');
        }
      } else {
        // Remove badge if no unread messages
        const badge = document.querySelector('.notification-badge');
        if (badge) badge.remove();
        const btn = document.getElementById('notificationBtn');
        btn.classList.remove('pulse');
      }
    });
}, 30000);
</script>

</body>
</html>