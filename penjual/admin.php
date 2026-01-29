<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}



// Update last_activity penjual yang sedang login
$id_penjual_login = $_SESSION['id_user'];
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='$id_penjual_login'");

// AMBIL DATA SEMUA PENJUAL (termasuk diri sendiri)
$penjual = mysqli_query($conn, "SELECT * FROM users WHERE role='penjual' ORDER BY id_user DESC");

// Ambil data penjual yang sedang login untuk highlight
$penjual_login = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_penjual_login'");
$data_login = mysqli_fetch_assoc($penjual_login);

// FUNGSI CEK STATUS YANG BENAR
function getStatus($last_activity, $status) {
    // 1. Cek status akun dulu
    if ($status !== 'aktif') {
        return 'nonaktif'; // Akun tidak aktif, tidak peduli last_activity
    }
    
    // 2. Jika akun aktif, baru cek online/offline
    if (!$last_activity) return 'offline';
    $last = strtotime($last_activity);
    $now  = time();
    $diff = $now - $last;
    return ($diff <= 300) ? 'online' : 'offline'; // 5 menit = online
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Penjual | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
   <link rel="stylesheet" href="../style/admin.css">
</head>
<body class="min-h-screen">

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
                <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-comments w-5"></i> Chat
                </a>
                <a href="penjual_view.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
                    <i class="fas fa-store w-5"></i> Daftar Penjual
                </a>
                <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-user-circle w-5"></i> Akun Saya
                </a>
                <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-question-circle w-5"></i> Bantuan
                </a>
            </nav>
        </div>

        <!-- PROFILE & LOGOUT -->
        <div class="p-4 border-t mt-auto">
            <div class="flex items-center gap-3 mb-4 p-3 bg-blue-50 rounded-lg">
                <div class="relative">
                    <?php if (!empty($data_login['foto'])): ?>
                        <img src="<?= htmlspecialchars($data_login['foto']) ?>" 
                             alt="Foto Profil" 
                             class="w-10 h-10 rounded-full object-cover border-2 border-white">
                    <?php else: ?>
                        <div class="w-10 h-10 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center">
                            <span class="text-white font-bold"><?= strtoupper(substr($data_login['nama'], 0, 1)) ?></span>
                        </div>
                    <?php endif; ?>
                    
                    <!-- LOGIKA ONLINE INDICATOR DI SIDEBAR -->
                    <?php 
                    $statusLogin = getStatus($data_login['last_activity'], $data_login['status']);
                    if ($statusLogin === 'online'): 
                    ?>
                        <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
                    <?php endif; ?>
                </div>
                <div class="flex-1">
                    <p class="font-medium text-sm text-gray-800 truncate"><?= htmlspecialchars($data_login['nama']) ?></p>
                    <p class="text-xs text-gray-500">Penjual</p>
                </div>
            </div>
            
            <a href="../auth/logout.php" onclick="return confirm('Yakin ingin keluar?')"
               class="flex items-center gap-3 text-red-500 hover:text-red-600">
                <i class="fas fa-sign-out-alt"></i> Keluar
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6 lg:p-8 overflow-y-auto ml-64">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Daftar Penjual</h2>
                <p class="text-gray-600">Lihat dan kenali penjual lain di platform Aksara Jiwa</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <button onclick="showOnlineOnly()" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <i class="fas fa-wifi"></i>
                    Tampilkan Online
                </button>
                <button onclick="showAll()" class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <i class="fas fa-users"></i>
                    Tampilkan Semua
                </button>
            </div>
        </div>

        <!-- STATS SUMMARY -->
        <?php 
        // Hitung statistik dengan LOGIKA YANG BENAR
        $totalPenjual = mysqli_num_rows($penjual);
        mysqli_data_seek($penjual, 0); // Reset pointer
        
        $aktifCount = 0;
        $onlineCount = 0;
        while ($row = mysqli_fetch_assoc($penjual)) {
            if ($row['status'] === 'aktif') {
                $aktifCount++;
                $statusCheck = getStatus($row['last_activity'], $row['status']);
                if ($statusCheck === 'online') $onlineCount++;
            }
        }
        mysqli_data_seek($penjual, 0); // Reset pointer lagi
        ?>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Total Penjual</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $totalPenjual ?></p>
                    </div>
                    <div class="w-12 h-12 bg-indigo-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-store text-indigo-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Penjual Aktif</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $aktifCount ?></p>
                    </div>
                    <div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-check-circle text-green-600 text-xl"></i>
                    </div>
                </div>
            </div>
            
            <div class="glass-card rounded-2xl p-6 hover-lift">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500 mb-1">Online Sekarang</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $onlineCount ?></p>
                    </div>
                    <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                        <i class="fas fa-wifi text-purple-600 text-xl"></i>
                    </div>
                </div>
            </div>
        </div>

        <!-- SEARCH BAR -->
        <div class="mb-6">
            <div class="relative max-w-md">
                <input type="text" 
                       id="searchInput" 
                       placeholder="Cari penjual berdasarkan nama atau email..." 
                       class="w-full px-4 py-3 pl-12 bg-white border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                <i class="fas fa-search absolute left-4 top-1/2 transform -translate-y-1/2 text-gray-400"></i>
            </div>
        </div>

        <!-- PENJUAL CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="penjualContainer">
            <?php while ($row = mysqli_fetch_assoc($penjual)) : 
                $isCurrentUser = ($row['id_user'] == $id_penjual_login);
                // PAKAI FUNGSI YANG SUDAH DIPERBAIKI
                $statusOnline = getStatus($row['last_activity'], $row['status']);
                $foto = !empty($row['foto']) ? $row['foto'] : '../assets/default_user.png';
            ?>
            <div class="glass-card rounded-2xl p-6 hover-lift <?= $isCurrentUser ? 'current-user-card' : '' ?>" 
                 data-name="<?= htmlspecialchars(strtolower($row['nama'])) ?>"
                 data-email="<?= htmlspecialchars(strtolower($row['email'])) ?>"
                 data-status="<?= $statusOnline ?>">
                
                <!-- Avatar & Status Indicator -->
                <div class="relative mb-4">
                    <div class="relative mx-auto w-24 h-24">
                        <?php if (file_exists($foto)): ?>
                            <img src="<?= htmlspecialchars($foto) ?>" 
                                 alt="Foto <?= htmlspecialchars($row['nama']) ?>" 
                                 class="w-full h-full rounded-full object-cover border-4 border-white shadow-lg">
                        <?php else: ?>
                            <div class="w-full h-full bg-gradient-to-r from-indigo-500 to-purple-600 rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <span class="text-white font-bold text-2xl"><?= strtoupper(substr($row['nama'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- ONLINE STATUS INDICATOR - LOGIKA YANG BENAR -->
                        <div class="absolute bottom-2 right-2">
                            <?php if ($row['status'] === 'aktif' && $statusOnline === 'online'): ?>
                                <div class="relative">
                                    <span class="pulse-online"></span>
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full opacity-50 animate-ping"></span>
                                </div>
                            <?php elseif ($row['status'] !== 'aktif'): ?>
                                <!-- Akun nonaktif -->
                                <span class="w-3 h-3 bg-red-500 rounded-full block"></span>
                            <?php else: ?>
                                <!-- Akun aktif tapi offline -->
                                <span class="w-3 h-3 bg-gray-400 rounded-full block"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="text-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800 mb-1 truncate">
                        <?= htmlspecialchars($row['nama']) ?>
                        <?php if ($isCurrentUser): ?>
                            <span class="text-xs font-normal text-indigo-600 ml-1">(Anda)</span>
                        <?php endif; ?>
                    </h3>
                    <p class="text-sm text-gray-500 mb-3 truncate">
                        <i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($row['email']) ?>
                    </p>
                    
                    <!-- STATUS BADGES - LOGIKA YANG BENAR -->
                    <div class="flex flex-wrap justify-center gap-2">
                        <!-- Badge Status Akun (Aktif/Nonaktif) -->
                        <span class="status-badge <?= $row['status'] === 'aktif' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                            <i class="fas fa-circle text-xs"></i>
                            <?= ucfirst($row['status']) ?>
                        </span>
                        
                        <!-- Badge Status Online/Offline/Nonaktif -->
                        <?php if ($row['status'] !== 'aktif'): ?>
                            <!-- Akun nonaktif -->
                            <span class="status-badge bg-red-100 text-red-600">
                                <i class="fas fa-ban text-xs"></i>
                                Nonaktif
                            </span>
                        <?php elseif ($statusOnline === 'online'): ?>
                            <!-- Akun aktif dan online -->
                            <span class="status-badge bg-blue-100 text-blue-600">
                                <i class="fas fa-wifi text-xs"></i>
                                Online
                            </span>
                        <?php else: ?>
                            <!-- Akun aktif tapi offline -->
                            <span class="status-badge bg-gray-100 text-gray-600">
                                <i class="fas fa-clock text-xs"></i>
                                Offline
                            </span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Additional Info -->
                <?php if (!empty($row['created_at'])): ?>
                <div class="pt-4 border-t border-gray-100 mb-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-500 flex items-center gap-1">
                            <i class="fas fa-calendar-plus"></i>
                            Bergabung
                        </span>
                        <span class="font-medium text-gray-700"><?= date('d M Y', strtotime($row['created_at'])) ?></span>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="flex gap-2">
                    <a href="detail_penjual_view.php?id=<?= $row['id_user'] ?>" 
                       class="flex-1 bg-gradient-to-r from-blue-500 to-blue-600 text-white px-4 py-2.5 rounded-xl font-medium text-center hover:shadow-lg transition-shadow flex items-center justify-center gap-2">
                       <i class="fas fa-eye"></i>
                       <span>Lihat Profil</span>
                    </a>
                    
                    <!-- Tombol Hapus DISABLED -->
                    <button class="flex-1 bg-white border border-gray-300 text-gray-500 px-4 py-2.5 rounded-xl font-medium text-center disabled-btn flex items-center justify-center gap-2"
                            title="Hanya Super Admin yang dapat menghapus penjual">
                       <i class="fas fa-trash"></i>
                       <span>Hapus</span>
                    </button>
                </div>
            </div>
            <?php endwhile; ?>
            
            <?php if (mysqli_num_rows($penjual) === 0): ?>
            <div class="col-span-full">
                <div class="glass-card rounded-2xl p-12 text-center">
                    <div class="w-20 h-20 mx-auto mb-6 bg-gray-100 rounded-full flex items-center justify-center">
                        <i class="fas fa-store text-gray-400 text-3xl"></i>
                    </div>
                    <h3 class="text-xl font-bold text-gray-800 mb-2">Belum Ada Penjual</h3>
                    <p class="text-gray-600">Tidak ada data penjual yang terdaftar di sistem.</p>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- FOOTER -->
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                © 2024 <span class="font-semibold text-indigo-600">Aksara Jiwa</span> - Platform Literasi Digital
                <br>
                <span class="text-xs mt-1 block">Penjual Dashboard • Daftar Penjual (Hanya Lihat)</span>
            </p>
        </div>
    </main>
</div>

<script>
    // Filter penjual berdasarkan pencarian
    document.getElementById('searchInput').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('#penjualContainer > div');
        
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const email = card.getAttribute('data-email');
            
            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    });
    
    // Tampilkan hanya yang online
    function showOnlineOnly() {
        const cards = document.querySelectorAll('#penjualContainer > div');
        
        cards.forEach(card => {
            const status = card.getAttribute('data-status');
            
            if (status === 'online') {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
        
        // Update tombol aktif
        document.querySelector('[onclick="showOnlineOnly()"]').classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white');
        document.querySelector('[onclick="showOnlineOnly()"]').classList.remove('bg-white', 'text-gray-700');
        document.querySelector('[onclick="showAll()"]').classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white');
        document.querySelector('[onclick="showAll()"]').classList.add('bg-white', 'text-gray-700');
    }
    
    // Tampilkan semua penjual
    function showAll() {
        const cards = document.querySelectorAll('#penjualContainer > div');
        
        cards.forEach(card => {
            card.style.display = 'block';
        });
        
        // Update tombol aktif
        document.querySelector('[onclick="showAll()"]').classList.add('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white');
        document.querySelector('[onclick="showAll()"]').classList.remove('bg-white', 'text-gray-700');
        document.querySelector('[onclick="showOnlineOnly()"]').classList.remove('bg-gradient-to-r', 'from-indigo-500', 'to-purple-600', 'text-white');
        document.querySelector('[onclick="showOnlineOnly()"]').classList.add('bg-white', 'text-gray-700');
    }
    
    // Alert untuk tombol yang disabled
    document.querySelectorAll('.disabled-btn').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            alert('Maaf, Anda tidak memiliki izin untuk melakukan aksi ini. Hanya Super Admin yang dapat mengedit atau menghapus data penjual.');
        });
    });
</script>
</body>
</html>