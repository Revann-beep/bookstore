<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
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
  <title>Dashboard | Aksara Jiwa</title>
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
    .gradient-bg {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    }
    .gradient-sidebar {
      background: linear-gradient(180deg, #4f46e5 0%, #7c3aed 100%);
    }
  </style>
</head>
<body class="bg-gray-50 min-h-screen">

<div class="flex min-h-screen">
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
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
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
        <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
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
      <a href="../auth/logout.php" onclick="logoutConfirm(); return false;"
         class="flex items-center gap-3 text-red-500 hover:text-red-600">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </a>
    </div>
  </aside>

  <!-- MAIN CONTENT -->
  <main class="flex-1 ml-64 p-6 overflow-y-auto h-screen">
    <!-- TOP BAR -->
    <div class="flex justify-between items-center mb-8">
      <!-- Welcome Message -->
      <div>
        <h1 class="text-2xl font-bold text-gray-800">Selamat Datang, <?= htmlspecialchars($user['nama']) ?>! 👋</h1>
        <p class="text-gray-600 mt-1">Ini adalah ringkasan performa toko Anda hari ini</p>
      </div>

      <div class="flex items-center gap-4">
        <!-- Search Bar -->
        <div class="relative">
          <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
            <i class="fas fa-search text-gray-400"></i>
          </div>
          <input type="text" placeholder="Cari produk, pesanan..."
            class="pl-10 pr-4 py-2.5 bg-white border border-gray-300 rounded-lg shadow-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 w-64">
        </div>

        <!-- Notification Bell -->
        <div class="relative">
          <button id="notificationBtn" 
                  class="w-10 h-10 flex items-center justify-center bg-gradient-to-r from-indigo-500 to-purple-600 text-white rounded-full relative hover:opacity-90 transition <?= $total_unread > 0 ? 'pulse' : '' ?>">
            <i class="fas fa-bell"></i>
            <?php if ($total_unread > 0): ?>
              <span class="notification-badge"><?= $total_unread > 99 ? '99+' : $total_unread ?></span>
            <?php endif; ?>
          </button>
          
          <!-- Notification Dropdown -->
          <div id="notificationDropdown" 
               class="hidden absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-xl border z-50 dropdown-notif">
            <div class="p-4 border-b">
              <div class="flex justify-between items-center">
                <h3 class="font-semibold text-gray-800">Notifikasi</h3>
                <?php if ($total_unread > 0): ?>
                  <span class="text-xs bg-indigo-100 text-indigo-600 px-2 py-1 rounded-full font-medium">
                    <?= $total_unread ?> baru
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
                     class="block p-4 border-b hover:bg-gray-50 transition">
                    <div class="flex items-start gap-3">
                      <div class="w-10 h-10 bg-gradient-to-r from-indigo-100 to-purple-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <i class="fas fa-user text-indigo-600"></i>
                      </div>
                      <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-start">
                          <h4 class="font-medium text-gray-900 truncate"><?= htmlspecialchars($chat['sender_nama']) ?></h4>
                          <span class="text-xs text-gray-500 whitespace-nowrap"><?= $time ?></span>
                        </div>
                        <p class="text-sm text-gray-600 mt-1 truncate"><?= htmlspecialchars($message) ?></p>
                        <?php if(!$chat['is_read']): ?>
                          <div class="flex items-center gap-1 mt-1">
                            <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                            <span class="text-xs text-red-600">Belum dibaca</span>
                          </div>
                        <?php endif; ?>
                      </div>
                    </div>
                  </a>
                <?php endwhile; ?>
              <?php else: ?>
                <div class="p-8 text-center">
                  <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-3">
                    <i class="fas fa-bell-slash text-2xl text-gray-400"></i>
                  </div>
                  <p class="text-gray-500">Tidak ada notifikasi</p>
                </div>
              <?php endif; ?>
            </div>
            
            <div class="p-4 border-t">
              <a href="chat.php?user=<?= $row['id_user'] ?>&id_produk=<?= $row['id_produk'] ?>"> 
                 class="block text-center text-indigo-600 hover:text-indigo-800 font-medium flex items-center justify-center gap-1">
                <span>Lihat semua pesan</span>
                <i class="fas fa-arrow-right text-sm"></i>
              </a>
            </div>
          </div>
        </div>

        <!-- User Profile -->
        <div class="flex items-center gap-3 bg-white px-4 py-2.5 rounded-xl shadow border border-gray-100">
          <div class="flex flex-col items-end">
            <span class="text-sm font-semibold text-gray-800">
              <?= htmlspecialchars($user['nama']) ?>
            </span>
            <span class="text-xs text-gray-500">Penjual</span>
          </div>
          <div class="relative">
            <img src="../img/<?= $user['image'] ?: 'default.png' ?>"
                 class="w-10 h-10 rounded-full object-cover border-2 border-white shadow"
                 onerror="this.src='../img/default.png'"
                 alt="Profile">
            <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
          </div>
        </div>
      </div>
    </div>

    <!-- CONTENT GRID -->
    <div class="grid grid-cols-1 xl:grid-cols-4 gap-6">

      <!-- CENTER -->
      <div class="xl:col-span-3 space-y-6">
        <div class="gradient-bg rounded-2xl p-8 text-white flex justify-between shadow-lg">
          <div class="max-w-lg">
            <h2 class="text-3xl font-bold mb-3">Aksara Jiwa</h2>
            <p class="text-lg opacity-90 mb-4">
              Tempat di mana setiap kata menemukan jiwanya, 
              dan setiap cerita menemukan pembacanya.
            </p>
            <div class="flex items-center gap-4">
              <?php if ($total_unread > 0): ?>
                <div class="bg-white text-indigo-600 px-4 py-2 rounded-lg font-medium flex items-center gap-2">
                  <i class="fas fa-comment-dots"></i>
                  <?= $total_unread ?> pesan baru
                </div>
              <?php endif; ?>
              <a href="chat.php" class="bg-white/20 hover:bg-white/30 px-4 py-2 rounded-lg transition flex items-center gap-2">
                <i class="fas fa-reply"></i>
                Balas Sekarang
              </a>
            </div>
          </div>
          <div class="hidden lg:block">
            <div class="w-32 h-32 bg-white/20 rounded-full flex items-center justify-center">
              <i class="fas fa-book-open text-5xl opacity-80"></i>
            </div>
          </div>
        </div>

        <!-- KATEGORI BUKU -->
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="flex justify-between items-center mb-6">
            <div>
              <h3 class="text-xl font-bold text-gray-800">Kategori Buku</h3>
              <p class="text-gray-600 text-sm mt-1">Kelola dan lihat kategori buku yang tersedia</p>
            </div>
            <a href="produk.php" class="text-indigo-600 hover:text-indigo-800 font-medium flex items-center gap-2">
              <span>Kelola Kategori</span>
              <i class="fas fa-arrow-right"></i>
            </a>
          </div>

          <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
            <?php 
            mysqli_data_seek($kategori, 0);
            while ($row = mysqli_fetch_assoc($kategori)) : 
            ?>
              <div class="bg-gradient-to-br from-indigo-50 to-purple-50 border border-indigo-100 p-4 rounded-xl shadow-sm hover:shadow-md transition hover:-translate-y-1 flex flex-col items-center">
                <div class="w-14 h-14 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-xl flex items-center justify-center mb-3 shadow">
                  <i class="fas fa-book text-white text-xl"></i>
                </div>
                <span class="mt-2 text-sm font-semibold text-gray-800 text-center">
                  <?= htmlspecialchars($row['nama_kategori']) ?>
                </span>
                <span class="text-xs text-gray-500 mt-1">Lihat produk</span>
              </div>
            <?php endwhile; ?>
          </div>
        </div>
      </div>

      <!-- RIGHT PANEL -->
      <div class="space-y-6">
        <!-- CALENDAR -->
        <div class="bg-gradient-to-br from-indigo-500 to-purple-600 rounded-2xl p-6 text-white shadow">
          <div class="flex items-center justify-between mb-4">
            <h4 class="font-bold">Kalender</h4>
            <i class="fas fa-calendar-alt text-xl opacity-80"></i>
          </div>
          <div class="text-center py-4">
            <p class="text-sm opacity-90 mb-1"><?= $bulan ?></p>
            <p class="text-5xl font-bold my-3"><?= $tanggal ?></p>
            <p class="text-lg font-medium"><?= $hari ?></p>
          </div>
          <div class="text-center text-sm opacity-90 mt-4">
            <i class="fas fa-clock mr-2"></i>
            <?= date('H:i') ?> WIB
          </div>
        </div>

        <!-- STATISTIK -->
        <div class="bg-white rounded-2xl shadow p-6">
          <div class="flex items-center gap-3 mb-6">
            <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-500 rounded-lg flex items-center justify-center">
              <i class="fas fa-chart-bar text-white"></i>
            </div>
            <div>
              <h4 class="font-bold text-gray-800">Statistik Hari Ini</h4>
              <p class="text-gray-600 text-sm">Update real-time</p>
            </div>
          </div>
          
          <div class="space-y-4">
            <div class="flex justify-between items-center p-3 bg-blue-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-shopping-cart text-blue-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Total Pesanan</p>
                  <p class="font-bold text-gray-800">12</p>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center p-3 bg-green-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-money-bill-wave text-green-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Pendapatan</p>
                  <p class="font-bold text-gray-800">Rp 1.250K</p>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center p-3 bg-purple-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-users text-purple-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Pembeli Aktif</p>
                  <p class="font-bold text-gray-800">8</p>
                </div>
              </div>
            </div>
            
            <div class="flex justify-between items-center p-3 bg-red-50 rounded-lg">
              <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-100 rounded-lg flex items-center justify-center">
                  <i class="fas fa-envelope text-red-600"></i>
                </div>
                <div>
                  <p class="text-sm text-gray-600">Pesan Baru</p>
                  <p class="font-bold <?= $total_unread > 0 ? 'text-red-600' : 'text-gray-800' ?>">
                    <?= $total_unread ?>
                  </p>
                </div>
              </div>
              <?php if ($total_unread > 0): ?>
                <a href="chat.php" class="text-red-600 hover:text-red-800 text-sm font-medium">
                  Balas <i class="fas fa-arrow-right ml-1"></i>
                </a>
              <?php endif; ?>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
function logoutConfirm(e) {
  e.preventDefault();
  Swal.fire({
    title: 'Yakin ingin keluar?',
    text: "Anda akan keluar dari sistem",
    icon: 'warning',
    showCancelButton: true,
    confirmButtonColor: '#4f46e5',
    cancelButtonColor: '#ef4444',
    confirmButtonText: 'Ya, Keluar',
    cancelButtonText: 'Batal',
    reverseButtons: true
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
  
  if (dropdown && btn && !dropdown.contains(e.target) && !btn.contains(e.target)) {
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