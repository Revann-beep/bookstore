<?php
session_start();
require '../auth/connection.php';

// Cek login dan role super admin
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit;
}

$id_user = $_SESSION['id_user'];



// Ambil data user yang login
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($query);

// Ambil statistik untuk dashboard
// Total Admin (exclude superadmin)
$adminQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='penjual'");
$adminData = mysqli_fetch_assoc($adminQuery);
$totalAdmin = $adminData['total'] ?? 0;

// Total Pembeli
$pembeliQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM users WHERE role='pembeli'");
$pembeliData = mysqli_fetch_assoc($pembeliQuery);
$totalPembeli = $pembeliData['total'] ?? 0;

// Total Kategori
$kategoriQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM kategori");
$kategoriData = mysqli_fetch_assoc($kategoriQuery);
$totalKategori = $kategoriData['total'] ?? 0;

// Update last activity
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='$id_user'");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Super Admin Dashboard | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            min-height: 100vh;
            overflow: hidden; /* Mencegah scroll global */
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
        .logo-icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
        .pulse-dot {
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
        
        /* SIDEBAR FIXED */
        .sidebar-container {
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            z-index: 40;
            overflow-y: auto; /* Scroll hanya di sidebar jika kontennya panjang */
            overflow-x: hidden;
        }
        
        /* MAIN CONTENT SCROLLABLE */
        .main-content {
            margin-left: 16rem; /* 256px = w-64 */
            height: 100vh;
            overflow-y: auto;
            overflow-x: hidden;
        }
        
        /* Custom scrollbar untuk main content */
        .main-content::-webkit-scrollbar {
            width: 8px;
        }
        .main-content::-webkit-scrollbar-track {
            background: #f1f5f9;
        }
        .main-content::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 4px;
        }
        .main-content::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }
        
        /* Custom scrollbar untuk sidebar */
        .sidebar-container::-webkit-scrollbar {
            width: 4px;
        }
        .sidebar-container::-webkit-scrollbar-track {
            background: transparent;
        }
        .sidebar-container::-webkit-scrollbar-thumb {
            background: #c7d2fe;
            border-radius: 4px;
        }
        .sidebar-container::-webkit-scrollbar-thumb:hover {
            background: #a5b4fc;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .sidebar-container {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar-container.mobile-open {
                transform: translateX(0);
            }
            .main-content {
                margin-left: 0;
            }
            .overlay {
                display: none;
                position: fixed;
                inset: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 30;
            }
            .overlay.active {
                display: block;
            }
        }
    </style>
</head>
<body class="min-h-screen overflow-hidden">

<!-- OVERLAY MOBILE -->
<div id="mobileOverlay" class="overlay"></div>

<!-- SIDEBAR FIXED -->
<aside class="sidebar-container w-64 glass-card flex flex-col shadow-xl">
    <!-- LOGO -->
    <div class="p-6 border-b border-gray-100">
        <div class="flex items-center gap-3 animate__animated animate__fadeIn">
            <div class="w-12 h-12 logo-icon text-white rounded-xl flex items-center justify-center shadow-md">
                <i class="fas fa-book-open text-lg"></i>
            </div>
            <div>
                <h1 class="font-bold text-xl text-gray-800">Aksara Jiwa</h1>
                <p class="text-xs text-gray-500 mt-1">Super Admin Dashboard</p>
            </div>
        </div>
    </div>

    <!-- NAVIGATION -->
    <nav class="flex-1 px-4 py-6 space-y-1">
        <a href="#" class="flex items-center px-4 py-3 rounded-xl sidebar-link active-link hover-lift">
            <i class="fas fa-tachometer-alt w-5 mr-3"></i>
            <span class="font-medium">Dashboard</span>
            <span class="ml-auto pulse-dot"></span>
        </a>

        <a href="penjual.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-user-shield w-5 mr-3 text-indigo-500"></i>
            <span>Penjual Management</span>
        </a>

        <a href="pembeli.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-users w-5 mr-3 text-emerald-500"></i>
            <span>Pembeli</span>
        </a>

        <a href="kategori.php" class="flex items-center px-4 py-3 rounded-xl sidebar-link text-gray-700 hover:bg-indigo-50 hover-lift">
            <i class="fas fa-tags w-5 mr-3 text-amber-500"></i>
            <span>Kategori</span>
        </a>

        
    </nav>

    <!-- FOOTER -->
    <div class="p-4 border-t border-gray-100 mt-auto">
        <div class="flex items-center gap-3 mb-4 px-2">
            <div class="relative">
                <?php if (!empty($user['image'])): ?>
                    <img src="<?= htmlspecialchars($user['image']) ?>" class="w-10 h-10 rounded-full border-2 border-white shadow">
                <?php else: ?>
                    <div class="w-10 h-10 gradient-primary rounded-full flex items-center justify-center text-white font-bold">
                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                    </div>
                <?php endif; ?>
                <span class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-white"></span>
            </div>
            <div class="flex-1">
                <p class="text-sm font-medium text-gray-800 truncate"><?= htmlspecialchars($user['nama']) ?></p>
                <p class="text-xs text-gray-500">Super Admin</p>
            </div>
        </div>
        <div class="flex justify-center space-x-4">
            <a href="../auth/logout.php" class="text-gray-400 hover:text-red-500 transition-colors" title="Sign Out">
                <i class="fas fa-sign-out-alt"></i>
            </a>
            <a href="help.php" class="text-gray-400 hover:text-blue-500 transition-colors" title="Help">
                <i class="fas fa-question-circle"></i>
            </a>
            <a href="#" class="text-gray-400 hover:text-green-500 transition-colors" title="Notifications">
                <i class="fas fa-bell"></i>
            </a>
        </div>
    </div>
</aside>

<!-- MAIN CONTENT SCROLLABLE -->
<main class="main-content p-6 lg:p-8 bg-gradient-to-br from-gray-50 to-blue-50">
    <!-- MOBILE MENU TOGGLE -->
    <div class="lg:hidden mb-6">
        <button id="menuToggle" class="p-2 rounded-lg bg-white shadow hover:shadow-md transition-shadow">
            <i class="fas fa-bars text-gray-600 text-xl"></i>
        </button>
    </div>

    <!-- TOP BAR -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 animate__animated animate__fadeInDown">
        <div class="mb-4 md:mb-0">
            <h2 class="text-2xl lg:text-3xl font-bold text-gray-800">Super Admin Dashboard</h2>
            <p class="text-gray-600 mt-1">Welcome back, <?= htmlspecialchars($user['nama']) ?>! Last login: <?= date('H:i') ?></p>
        </div>
        <div class="flex items-center gap-3 text-sm text-gray-500 bg-white px-4 py-2 rounded-full shadow-sm">
            <i class="fas fa-calendar-alt"></i>
            <span><?= date('l, d F Y') ?></span>
            <span class="hidden md:inline">•</span>
            <span class="hidden md:inline time-display time"><?= date('H:i:s') ?></span>
        </div>
    </div>

    <!-- HERO SECTION -->
    <div class="gradient-primary rounded-2xl p-6 md:p-8 text-white shadow-xl hover-lift animate__animated animate__fadeInUp mb-8">
        <div class="flex flex-col lg:flex-row justify-between items-center">
            <div class="lg:w-2/3">
                <h1 class="text-2xl md:text-3xl lg:text-4xl font-bold mb-4">Welcome to Aksara Jiwa Admin Panel</h1>
                <p class="text-base md:text-lg opacity-90 mb-6 leading-relaxed">
                    Platform literasi terdepan yang menghubungkan penulis dengan pembaca.
                    Anda memiliki akses penuh untuk mengelola seluruh sistem.
                </p>
                <div class="flex flex-wrap gap-3">
                    <button class="bg-white text-indigo-600 px-4 md:px-6 py-2 md:py-3 rounded-xl font-semibold shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 text-sm md:text-base">
                        <i class="fas fa-rocket mr-2"></i>Quick Tour
                    </button>
                    <a href="admin.php" class="bg-transparent border-2 border-white/30 text-white px-4 md:px-6 py-2 md:py-3 rounded-xl font-medium hover:bg-white/10 transition-all duration-300 text-sm md:text-base">
                        <i class="fas fa-user-shield mr-2"></i>Manage Admins
                    </a>
                    <a href="analytics.php" class="bg-transparent border-2 border-white/30 text-white px-4 md:px-6 py-2 md:py-3 rounded-xl font-medium hover:bg-white/10 transition-all duration-300 text-sm md:text-base">
                        <i class="fas fa-chart-line mr-2"></i>View Analytics
                    </a>
                </div>
            </div>
            <div class="mt-8 lg:mt-0 lg:pl-8">
                <div class="relative">
                    <div class="w-32 h-32 md:w-48 md:h-48 bg-white/10 rounded-full flex items-center justify-center backdrop-blur-sm">
                        <i class="fas fa-crown text-4xl md:text-6xl text-white/80"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- QUICK STATS -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 mb-8">
        <a href="penjual.php" class="glass-card rounded-2xl p-5 md:p-6 hover-lift block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Penjual</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800"><?= $totalAdmin ?></p>
                    <p class="text-xs text-gray-400 mt-1">Excluding Super Admin</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-blue-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-user-shield text-blue-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </a>
        
        <a href="pembeli.php" class="glass-card rounded-2xl p-5 md:p-6 hover-lift block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Pembeli</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800"><?= $totalPembeli ?></p>
                    <p class="text-xs text-gray-400 mt-1">Registered Users</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-green-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-users text-green-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </a>
        
        <a href="kategori.php" class="glass-card rounded-2xl p-5 md:p-6 hover-lift block">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-500 mb-1">Total Kategori</p>
                    <p class="text-xl md:text-2xl font-bold text-gray-800"><?= $totalKategori ?></p>
                    <p class="text-xs text-gray-400 mt-1">Product Categories</p>
                </div>
                <div class="w-10 h-10 md:w-12 md:h-12 bg-purple-100 rounded-xl flex items-center justify-center">
                    <i class="fas fa-tags text-purple-600 text-lg md:text-xl"></i>
                </div>
            </div>
        </a>
    </div>

    <!-- ACCOUNT INFO CARD -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4 flex items-center gap-2">
            <i class="fas fa-user-circle text-indigo-500"></i>
            My Account Information
        </h3>
        
        <div class="glass-card rounded-2xl p-5 md:p-6 shadow-lg hover-lift animate__animated animate__fadeIn">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5 md:gap-6">
                <div>
                    <div class="flex items-center gap-4 mb-6">
                        <div class="relative">
                            <?php if (!empty($user['image'])): ?>
                                <img src="<?= htmlspecialchars($user['image']) ?>" class="w-16 h-16 md:w-20 md:h-20 rounded-full border-4 border-white shadow-lg">
                            <?php else: ?>
                                <div class="w-16 h-16 md:w-20 md:h-20 gradient-accent rounded-full flex items-center justify-center shadow-md">
                                    <span class="text-xl md:text-2xl font-bold text-white"><?= strtoupper(substr($user['nama'], 0, 1)) ?></span>
                                </div>
                            <?php endif; ?>
                            <div class="absolute bottom-1 right-1 w-5 h-5 md:w-6 md:h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                                <i class="fas fa-check text-white text-xs"></i>
                            </div>
                        </div>
                        <div>
                            <h4 class="text-lg md:text-xl font-bold text-gray-800">Super Admin Profile</h4>
                            <p class="text-gray-600 text-sm md:text-base">Highest privilege level</p>
                            <div class="flex items-center gap-2 mt-2">
                                <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-2 md:px-3 py-1 rounded-full text-xs font-medium">
                                    Full Access
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="space-y-3 md:space-y-4">
                        <div class="flex items-center justify-between p-3 md:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                            <div class="flex items-center gap-2 md:gap-3">
                                <i class="fas fa-id-card text-gray-500 text-sm md:text-base"></i>
                                <span class="text-gray-700 text-sm md:text-base">Full Name</span>
                            </div>
                            <span class="font-semibold text-gray-800 text-sm md:text-base truncate ml-2"><?= htmlspecialchars($user['nama']) ?></span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 md:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                            <div class="flex items-center gap-2 md:gap-3">
                                <i class="fas fa-shield-alt text-gray-500 text-sm md:text-base"></i>
                                <span class="text-gray-700 text-sm md:text-base">Access Level</span>
                            </div>
                            <span class="bg-gradient-to-r from-purple-600 to-pink-600 text-white px-3 md:px-4 py-1 rounded-full text-xs md:text-sm font-medium">
                                Super Admin
                            </span>
                        </div>
                        
                        <div class="flex items-center justify-between p-3 md:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                            <div class="flex items-center gap-2 md:gap-3">
                                <i class="fas fa-envelope text-gray-500 text-sm md:text-base"></i>
                                <span class="text-gray-700 text-sm md:text-base">Email Address</span>
                            </div>
                            <span class="font-semibold text-gray-800 text-sm md:text-base truncate ml-2"><?= htmlspecialchars($user['email']) ?></span>
                        </div>

                        <?php if (!empty($user['created_at'])): ?>
                        <div class="flex items-center justify-between p-3 md:p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-xl">
                            <div class="flex items-center gap-2 md:gap-3">
                                <i class="fas fa-calendar-plus text-gray-500 text-sm md:text-base"></i>
                                <span class="text-gray-700 text-sm md:text-base">Joined Date</span>
                            </div>
                            <span class="font-semibold text-gray-800 text-sm md:text-base"><?= date('d M Y', strtotime($user['created_at'])) ?></span>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="flex flex-col justify-between">
                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 p-4 md:p-6 rounded-xl border border-indigo-100 mb-4 md:mb-0">
                        <h5 class="font-semibold text-gray-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-key text-amber-500"></i>
                            Account Security
                        </h5>
                        <p class="text-xs md:text-sm text-gray-600 mb-4">
                            Your account has the highest level of access. Ensure you follow security best practices.
                        </p>
                        <div class="space-y-2 md:space-y-3">
                            <div class="flex items-center justify-between text-xs md:text-sm">
                                <span class="text-gray-600">Last Activity</span>
                                <span class="font-medium text-gray-800">Just now</span>
                            </div>
                            <div class="flex items-center justify-between text-xs md:text-sm">
                                <span class="text-gray-600">Session Status</span>
                                <span class="flex items-center gap-1 text-green-600">
                                    <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                                    Active
                                </span>
                            </div>
                            <div class="flex items-center justify-between text-xs md:text-sm">
                                <span class="text-gray-600">Account Status</span>
                                <span class="flex items-center gap-1 text-green-600">
                                    <i class="fas fa-check-circle text-xs md:text-sm"></i>
                                    Verified
                                </span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4 md:mt-6 flex flex-col gap-2 md:gap-3">
                        <a href="edit-sa.php" class="gradient-accent text-white px-4 md:px-6 py-2 md:py-3 rounded-xl font-medium shadow-md hover:shadow-lg transform hover:-translate-y-1 transition-all duration-300 flex items-center justify-center gap-2 text-sm md:text-base">
                            <i class="fas fa-edit"></i>
                            Edit Profile
                        </a>
                        <a href="help.php" class="border-2 border-gray-300 text-gray-700 px-4 md:px-6 py-2 md:py-3 rounded-xl font-medium hover:bg-gray-50 transition-all duration-300 flex items-center justify-center gap-2 text-sm md:text-base">
                            <i class="fas fa-question-circle"></i>
                            Get Help
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <div class="text-center py-4 md:py-6 border-t border-gray-200 mt-6 md:mt-8">
        <p class="text-xs md:text-sm text-gray-500">
            © 2024 <span class="font-semibold text-indigo-600">Aksara Jiwa</span> - Platform Literasi Digital
            <br>
            <span class="text-xs mt-1 block">Logged in as Super Admin • Session active</span>
        </p>
    </div>

</main>

<script>
    // Mobile menu toggle
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.querySelector('.sidebar-container');
    const overlay = document.getElementById('mobileOverlay');
    
    if (menuToggle) {
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('mobile-open');
            overlay.classList.toggle('active');
        });
    }
    
    if (overlay) {
        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        });
    }
    
    // Active link indication
    const currentPage = window.location.pathname.split('/').pop();
    document.querySelectorAll('.sidebar-link').forEach(link => {
        const linkPage = link.getAttribute('href');
        if (linkPage === currentPage || (currentPage === '' && linkPage === '#')) {
            link.classList.add('active-link');
            link.classList.remove('text-gray-700', 'hover:bg-indigo-50');
        }
    });
    
    // Update time display
    function updateTime() {
        const now = new Date();
        const timeString = now.toLocaleTimeString('id-ID');
        const timeElements = document.querySelectorAll('.time-display.time');
        timeElements.forEach(el => {
            el.textContent = timeString;
        });
    }
    
    // Update time every second
    setInterval(updateTime, 1000);
    updateTime(); // Initial call
    
    // Close mobile menu on window resize
    window.addEventListener('resize', () => {
        if (window.innerWidth > 768) {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('active');
        }
    });
</script>
</body>
</html>