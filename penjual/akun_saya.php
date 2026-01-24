<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}



$id_user = $_SESSION['id_user'];

// update last_activity
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='$id_user'");

// ambil data diri sendiri
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($query);

// AMBIL DATA PRODUK AKTIF
$produkAktifQuery = mysqli_query($conn, "
    SELECT COUNT(*) as total_produk
    FROM produk 
    WHERE id_penjual = '$id_user' 
    AND stok > 0
");
$produkAktif = mysqli_fetch_assoc($produkAktifQuery);
$totalProdukAktif = $produkAktif['total_produk'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e4edf5 100%);
            min-height: 100vh;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 8px 32px rgba(31, 38, 135, 0.1);
        }
        .gradient-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .gradient-success {
            background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%);
        }
        .gradient-warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        .gradient-info {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        .hover-lift {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
        }
        .profile-img {
            border: 4px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }
        .status-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .online {
            background-color: #10b981;
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.3);
        }
        .offline {
            background-color: #9ca3af;
        }
    </style>
</head>
<body class="min-h-screen">

<div class="flex flex-col lg:flex-row min-h-screen">

<!-- HEADER MOBILE -->
<div class="lg:hidden bg-white shadow-md p-4 flex items-center justify-between">
    <div class="flex items-center gap-3">
        <div class="w-10 h-10 gradient-primary rounded-xl flex items-center justify-center">
            <i class="fas fa-book text-white"></i>
        </div>
        <div>
            <h2 class="font-bold text-gray-800">Aksara Jiwa</h2>
            <p class="text-xs text-gray-500">Penjual Dashboard</p>
        </div>
    </div>
    <button id="menuToggle" class="text-gray-600">
        <i class="fas fa-bars text-xl"></i>
    </button>
</div>

<!-- SIDEBAR -->
<aside id="sidebar" class="w-64 bg-white shadow-xl lg:shadow-lg flex flex-col fixed lg:relative inset-y-0 left-0 transform -translate-x-full lg:translate-x-0 transition-transform duration-300 z-50">
    <!-- LOGO -->
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3">
            <div class="w-12 h-12 gradient-primary text-white rounded-xl flex items-center justify-center animate__animated animate__pulse">
                <i class="fas fa-book text-xl"></i>
            </div>
            <div>
                <h2 class="font-bold text-gray-800 text-lg">Aksara Jiwa</h2>
                <p class="text-xs text-gray-500">Penjual Dashboard</p>
            </div>
        </div>
    </div>

    <!-- MENU -->
    <div class="flex-1 px-4 py-6 space-y-1">
        <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-50 text-gray-700 transition-colors duration-200">
            <i class="fas fa-chart-line w-5 text-indigo-500"></i>
            <span>Dashboard</span>
        </a>

          
        
        <a href="produk.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-50 text-gray-700 transition-colors duration-200">
            <i class="fas fa-box-open w-5 text-emerald-500"></i>
            <span>Produk</span>
        </a>
        
        <div class="border border-gray-100 rounded-xl mt-4">
            <button onclick="toggleApprove()" class="w-full flex items-center justify-between px-4 py-3 text-gray-700 hover:bg-indigo-50 rounded-xl transition-colors duration-200">
                <span class="flex items-center gap-3">
                    <i class="fas fa-check-circle w-5 text-amber-500"></i>
                    <span>Approval</span>
                </span>
                <span id="iconApprove" class="text-gray-400">▼</span>
            </button>
            
            <div id="approveMenu" class="hidden px-4 pb-3 space-y-2">
                <a href="approve.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm transition-colors duration-200">Approve Pesanan</a>
                <a href="laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm transition-colors duration-200">Laporan</a>
                <a href="chat.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100 text-sm transition-colors duration-200">Chat</a>
            </div>
        </div>
       
       <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-indigo-50 text-gray-700 transition-colors duration-200">
            <i class="fas fa-store w-5"></i>
            <span>Data Penjual</span>
        </a> 
        
        <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-indigo-50 to-purple-50 text-indigo-600 font-medium border border-indigo-100">
            <i class="fas fa-user-circle w-5 text-indigo-600"></i>
            <span>Akun Saya</span>
        </a>
        
        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl hover:bg-red-50 text-red-500 mt-8 transition-colors duration-200">
            <i class="fas fa-sign-out-alt w-5"></i>
            <span>Keluar</span>
        </a>
    </div>

    <!-- FOOTER SIDEBAR -->
    <div class="p-4 border-t border-gray-100">
        <div class="text-center text-xs text-gray-500">
            <p>© 2024 Aksara Jiwa</p>
            <p class="mt-1">Versi 2.1.0</p>
        </div>
    </div>
</aside>

<!-- OVERLAY MOBILE -->
<div id="overlay" class="fixed inset-0 bg-black bg-opacity-50 z-40 lg:hidden hidden"></div>

<!-- MAIN CONTENT -->
<main class="flex-1 p-4 lg:p-8">
    <!-- HEADER -->
    <div class="mb-8 animate__animated animate__fadeIn">
        <h1 class="text-3xl lg:text-4xl font-bold text-gray-800 mb-2">Akun Saya</h1>
        <p class="text-gray-600">Kelola informasi dan preferensi akun Anda</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <!-- PROFILE CARD -->
        <div class="glass-card rounded-2xl p-6 lg:col-span-2 hover-lift">
            <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
                <div class="relative">
                    <img src="<?= htmlspecialchars($user['image'] ?: '../img/default_user.png') ?>"
                         class="w-32 h-32 rounded-full object-cover profile-img animate__animated animate__pulse">
                    <div class="absolute bottom-3 right-3 w-10 h-10 gradient-success rounded-full border-4 border-white flex items-center justify-center animate__animated animate__bounceIn">
                        <i class="fas fa-check text-white"></i>
                    </div>
                </div>
                
                <div class="flex-1 text-center md:text-left">
                    <h2 class="text-2xl lg:text-3xl font-bold mb-2 text-gray-800"><?= htmlspecialchars($user['nama']) ?></h2>
                    <p class="text-gray-600 mb-4 flex items-center justify-center md:justify-start">
                        <i class="fas fa-envelope mr-2 text-indigo-500"></i>
                        <?= htmlspecialchars($user['email']) ?>
                    </p>
                    
                    <div class="inline-flex items-center gap-2 gradient-primary text-white px-5 py-2 rounded-full shadow-md mb-4">
                        <i class="fas fa-store text-sm"></i>
                        <span class="font-medium">Penjual Terverifikasi</span>
                    </div>
                    
                    <div class="mt-6 bg-gray-50 p-4 rounded-xl">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <i class="fas fa-calendar-alt text-gray-500"></i>
                                <span class="text-sm text-gray-600">Bergabung sejak:</span>
                            </div>
                            <span class="font-medium text-gray-800"><?= date('d M Y', strtotime($user['created_at'] ?? 'now')) ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- STATS CARD -->
        <div class="glass-card rounded-2xl p-6 hover-lift">
            <h3 class="font-bold text-gray-800 mb-6 text-lg flex items-center gap-2">
                <i class="fas fa-chart-bar text-indigo-500"></i>
                Statistik Akun
            </h3>
            <div class="space-y-5">
                <div class="flex items-center justify-between p-4 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl border border-blue-100">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 gradient-info rounded-xl flex items-center justify-center shadow-md">
                            <i class="fas fa-box text-white text-lg"></i>
                        </div>
                        <div>
                            <p class="text-sm text-gray-600">Produk Aktif</p>
                            <p class="font-bold text-2xl text-gray-800"><?= $totalProdukAktif ?></p>
                        </div>
                    </div>
                    <a href="produk.php" class="text-blue-600 hover:text-blue-800 transform hover:scale-110 transition-transform duration-200">
                        <i class="fas fa-arrow-right text-lg"></i>
                    </a>
                </div>
                
                <div class="text-center p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl border border-gray-200">
                    <p class="text-sm text-gray-500 mb-2">Status Akun</p>
                    <div class="flex items-center justify-center gap-2">
                        <span class="status-dot online animate-pulse"></span>
                        <span class="font-medium text-green-600">Aktif</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DETAIL INFO -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        <div class="glass-card rounded-2xl p-6 hover-lift">
            <h3 class="font-bold text-gray-800 mb-6 text-lg flex items-center gap-2">
                <i class="fas fa-user-circle text-indigo-500"></i>
                Informasi Akun
            </h3>
            <div class="space-y-5">
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user text-gray-500"></i>
                        <span class="text-gray-600">Nama Lengkap</span>
                    </div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($user['nama']) ?></span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-envelope text-gray-500"></i>
                        <span class="text-gray-600">Email</span>
                    </div>
                    <span class="font-medium text-gray-800"><?= htmlspecialchars($user['email']) ?></span>
                </div>
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded-lg">
                    <div class="flex items-center gap-3">
                        <i class="fas fa-user-tag text-gray-500"></i>
                        <span class="text-gray-600">Role</span>
                    </div>
                    <span class="bg-gradient-to-r from-indigo-500 to-purple-600 text-white px-4 py-1.5 rounded-full text-sm font-medium">Penjual</span>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-2xl p-6 hover-lift">
            <h3 class="font-bold text-gray-800 mb-6 text-lg flex items-center gap-2">
                <i class="fas fa-cogs text-indigo-500"></i>
                Pengaturan & Aksi
            </h3>
            <div class="space-y-4">
                <a href="../auth/edit_akun.php" class="block gradient-primary text-white text-center py-3.5 rounded-xl font-medium shadow-md hover:shadow-lg transition-all duration-300 transform hover:-translate-y-1">
                    <i class="fas fa-user-edit mr-2"></i>Edit Profil
                </a>
                <a href="#" class="block border-2 border-gray-200 text-gray-700 text-center py-3.5 rounded-xl font-medium hover:bg-gray-50 hover:border-indigo-300 transition-all duration-300">
                    <i class="fas fa-key mr-2"></i>Ubah Password
                </a>
                <a href="help.php" class="block border-2 border-gray-200 text-gray-700 text-center py-3.5 rounded-xl font-medium hover:bg-gray-50 hover:border-emerald-300 transition-all duration-300">
                    <i class="fas fa-question-circle mr-2"></i>Pusat Bantuan
                </a>
            </div>
        </div>
    </div>

    <!-- LAST ACTIVITY -->
    <div class="glass-card rounded-2xl p-6 animate__animated animate__fadeInUp">
        <div class="flex flex-col md:flex-row items-center justify-between">
            <div class="mb-4 md:mb-0">
                <h3 class="font-bold text-gray-800 text-lg mb-2">Status Aktivitas</h3>
                <p class="text-gray-600">Akun Anda aktif dalam 24 jam terakhir</p>
            </div>
            <div class="flex items-center gap-3 bg-gradient-to-r from-green-50 to-emerald-50 px-5 py-3 rounded-xl border border-green-100">
                <div class="relative">
                    <span class="status-dot online animate-pulse"></span>
                </div>
                <div>
                    <span class="font-bold text-green-700 text-lg">Online</span>
                    <p class="text-xs text-green-600">Terakhir aktif: Sekarang</p>
                </div>
            </div>
        </div>
    </div>
</main>
</div>

<script>
    // Toggle mobile menu
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('overlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('-translate-x-full');
            overlay.classList.toggle('hidden');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.add('-translate-x-full');
            overlay.classList.add('hidden');
        });
    }
    
    // Toggle approve menu
    function toggleApprove() {
        const menu = document.getElementById('approveMenu');
        const icon = document.getElementById('iconApprove');
        menu.classList.toggle('hidden');
        icon.textContent = menu.classList.contains('hidden') ? '▼' : '▲';
    }
    
    // Animation on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };
    
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate__animated', 'animate__fadeInUp');
            }
        });
    }, observerOptions);
    
    // Observe cards
    document.querySelectorAll('.glass-card').forEach(card => {
        observer.observe(card);
    });
</script>
</body>
</html>