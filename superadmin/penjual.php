<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN SUPER ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit;
}

// Update last_activity Super Admin sendiri (opsional)
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='{$_SESSION['id_user']}'");

// HAPUS PENJUAL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user='$id' AND role='penjual'");
    header("Location: penjual.php");
    exit;
}

// AMBIL DATA PENJUAL
$penjual = mysqli_query($conn, "SELECT * FROM users WHERE role='penjual' ORDER BY id_user DESC");

// Fungsi untuk cek status online/offline
function getStatus($last_activity) {
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
    <title>Data Penjual | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.08);
        }
        .gradient-primary {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        .gradient-accent {
            background: linear-gradient(135deg, #0ea5e9 0%, #3b82f6 100%);
        }
        .gradient-success {
            background: linear-gradient(135deg, #10b981 0%, #34d399 100%);
        }
        .hover-lift {
            transition: all 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
        }
        .sidebar-link {
            position: relative;
            overflow: hidden;
        }
        .sidebar-link::after {
            content: '';
            position: absolute;
            left: 0;
            bottom: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(90deg, #4f46e5, #7c3aed);
            transition: width 0.3s ease;
        }
        .sidebar-link:hover::after {
            width: 100%;
        }
        .active-link {
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
        }
        .pulse-online {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 0.25rem;
        }
        .avatar-placeholder {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
    </style>
</head>
<body class="min-h-screen">

<div class="flex min-h-screen">
    <!-- SIDEBAR -->
    <aside class="w-64 glass-card flex flex-col shadow-xl">
        <div class="p-6 border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 gradient-primary text-white rounded-xl flex items-center justify-center shadow-md">
                    <i class="fas fa-book-open text-lg"></i>
                </div>
                <div>
                    <h1 class="font-bold text-xl text-gray-800">Aksara Jiwa</h1>
                    <p class="text-xs text-gray-500 mt-1">Super Admin Dashboard</p>
                </div>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-1">
            <a href="dashboard.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
                <i class="fas fa-tachometer-alt w-5 mr-3 text-gray-500"></i>
                <span>Dashboard</span>
            </a>
            <a href="penjual.php" class="flex items-center px-4 py-3 rounded-xl active-link hover-lift">
                <i class="fas fa-store w-5 mr-3"></i>
                <span class="font-medium">Data Penjual</span>
            </a>
            <a href="pembeli.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
                <i class="fas fa-users w-5 mr-3 text-blue-500"></i>
                <span>Data Pembeli</span>
            </a>
            <a href="kategori.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
                <i class="fas fa-tags w-5 mr-3 text-amber-500"></i>
                <span>Kategori</span>
            </a>
        </nav>

        <div class="p-4 border-t border-gray-100 mt-auto">
            <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-700 hover:bg-red-50 hover:text-red-600 transition-colors duration-200">
                <i class="fas fa-sign-out-alt w-5"></i>
                <span>Sign Out</span>
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-6 lg:p-8 overflow-y-auto">
        <!-- HEADER -->
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div>
                <h2 class="text-2xl lg:text-3xl font-bold text-gray-800 mb-2">Data Akun Penjual</h2>
                <p class="text-gray-600">Kelola dan pantau semua akun penjual di platform</p>
            </div>
            <div class="mt-4 md:mt-0 flex items-center gap-3">
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <i class="fas fa-plus"></i>
                    Tambah Penjual
                </button>
                <button class="px-4 py-2 bg-white border border-gray-300 rounded-xl text-gray-700 hover:bg-gray-50 transition-colors flex items-center gap-2">
                    <i class="fas fa-download"></i>
                    Export Data
                </button>
            </div>
        </div>

        <!-- STATS SUMMARY -->
        <?php 
        // Hitung statistik
        $totalPenjual = mysqli_num_rows($penjual);
        mysqli_data_seek($penjual, 0); // Reset pointer
        
        $aktifCount = 0;
        $onlineCount = 0;
        while ($row = mysqli_fetch_assoc($penjual)) {
            if ($row['status'] === 'aktif') $aktifCount++;
            if (getStatus($row['last_activity']) === 'online') $onlineCount++;
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

        <!-- PENJUAL CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            <?php while ($row = mysqli_fetch_assoc($penjual)) : 
                $statusOnline = getStatus($row['last_activity']);
                $foto = !empty($row['foto']) ? $row['foto'] : '../assets/default_user.png';
            ?>
            <div class="glass-card rounded-2xl p-6 hover-lift">
                <!-- Avatar & Status -->
                <div class="relative mb-4">
                    <div class="relative mx-auto w-24 h-24">
                        <?php if (file_exists($foto)): ?>
                            <img src="<?= htmlspecialchars($foto) ?>" 
                                 alt="Foto <?= htmlspecialchars($row['nama']) ?>" 
                                 class="w-full h-full rounded-full object-cover border-4 border-white shadow-lg">
                        <?php else: ?>
                            <div class="w-full h-full avatar-placeholder rounded-full flex items-center justify-center border-4 border-white shadow-lg">
                                <span class="text-white font-bold text-2xl"><?= strtoupper(substr($row['nama'], 0, 1)) ?></span>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Online Status Indicator -->
                        <div class="absolute bottom-2 right-2">
                            <?php if ($statusOnline === 'online'): ?>
                                <div class="relative">
                                    <span class="pulse-online"></span>
                                    <span class="absolute -top-1 -right-1 w-4 h-4 bg-green-500 rounded-full opacity-50 animate-ping"></span>
                                </div>
                            <?php else: ?>
                                <span class="w-3 h-3 bg-gray-400 rounded-full block"></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                
                <!-- User Info -->
                <div class="text-center mb-4">
                    <h3 class="font-bold text-lg text-gray-800 mb-1 truncate"><?= htmlspecialchars($row['nama']) ?></h3>
                    <p class="text-sm text-gray-500 mb-3 truncate">
                        <i class="fas fa-envelope mr-2"></i><?= htmlspecialchars($row['email']) ?>
                    </p>
                    
                    <!-- Status Badges -->
                    <div class="flex flex-wrap justify-center gap-2">
                        <span class="status-badge <?= $row['status'] === 'aktif' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                            <i class="fas fa-circle text-xs"></i>
                            <?= ucfirst($row['status']) ?>
                        </span>
                        
                        <span class="status-badge <?= $statusOnline === 'online' ? 'bg-blue-100 text-blue-600' : 'bg-gray-100 text-gray-600' ?>">
                            <?php if ($statusOnline === 'online'): ?>
                                <i class="fas fa-wifi text-xs"></i>
                            <?php else: ?>
                                <i class="fas fa-clock text-xs"></i>
                            <?php endif; ?>
                            <?= ucfirst($statusOnline) ?>
                        </span>
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
                    <a href="detail_penjual.php?id=<?= $row['id_user'] ?>" 
                       class="flex-1 gradient-accent text-white px-4 py-2.5 rounded-xl font-medium text-center hover:shadow-lg transition-shadow flex items-center justify-center gap-2">
                       <i class="fas fa-eye"></i>
                       <span>Detail</span>
                    </a>
                    <a href="?hapus=<?= $row['id_user'] ?>" 
                       onclick="return confirm('Yakin hapus penjual <?= htmlspecialchars($row['nama']) ?>?')"
                       class="flex-1 bg-white border border-red-300 text-red-600 px-4 py-2.5 rounded-xl font-medium text-center hover:bg-red-50 transition-colors flex items-center justify-center gap-2">
                       <i class="fas fa-trash"></i>
                       <span>Hapus</span>
                    </a>
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
                    <button class="mt-6 px-6 py-3 gradient-primary text-white rounded-xl font-medium hover:shadow-lg transition-shadow">
                        <i class="fas fa-plus mr-2"></i>Tambah Penjual Pertama
                    </button>
                </div>
            </div>
            <?php endif; ?>
        </div>
        
        <!-- FOOTER -->
        <div class="mt-8 pt-6 border-t border-gray-200 text-center">
            <p class="text-sm text-gray-500">
                © 2024 <span class="font-semibold text-indigo-600">Aksara Jiwa</span> - Platform Literasi Digital
                <br>
                <span class="text-xs mt-1 block">Super Admin Dashboard • Data Penjual</span>
            </p>
        </div>
    </main>
</div>

<script>
    // Confirm delete
    document.querySelectorAll('a[onclick*="confirm"]').forEach(link => {
        link.addEventListener('click', function(e) {
            const confirmMessage = this.getAttribute('onclick').match(/'([^']+)'/)[1];
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });
    });
</script>
</body>
</html>