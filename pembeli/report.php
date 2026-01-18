<?php
session_start();
require '../auth/connection.php';

// CEK LOGIN ADMIN
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../index.php");
    exit;
}

$admin_id = $_SESSION['id_user'];
$admin_nama = $_SESSION['nama'];

/* AMBIL STATISTIK REAL-TIME */
// Total Orders
$totalOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders");
$totalOrders = mysqli_fetch_assoc($totalOrdersQuery)['total'] ?? 0;

// Total Revenue
$totalRevenueQuery = mysqli_query($conn, "SELECT SUM(total_harga) as total FROM orders WHERE status = 'paid'");
$totalRevenue = mysqli_fetch_assoc($totalRevenueQuery)['total'] ?? 0;

// Active Customers
$activeCustomersQuery = mysqli_query($conn, "SELECT COUNT(DISTINCT id_pembeli) as total FROM orders WHERE status IN ('paid', 'shipped', 'delivered')");
$activeCustomers = mysqli_fetch_assoc($activeCustomersQuery)['total'] ?? 0;

// Monthly Revenue
$currentMonth = date('m');
$currentYear = date('Y');
$monthlyRevenueQuery = mysqli_query($conn, "
    SELECT SUM(total_harga) as total 
    FROM orders 
    WHERE status = 'paid' 
    AND MONTH(created_at) = '$currentMonth' 
    AND YEAR(created_at) = '$currentYear'
");
$monthlyRevenue = mysqli_fetch_assoc($monthlyRevenueQuery)['total'] ?? 0;

// Pending Orders
$pendingOrdersQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders WHERE status = 'pending'");
$pendingOrders = mysqli_fetch_assoc($pendingOrdersQuery)['total'] ?? 0;

// Sales Growth (last month vs current month)
$lastMonth = date('m', strtotime('-1 month'));
$lastMonthRevenueQuery = mysqli_query($conn, "
    SELECT SUM(total_harga) as total 
    FROM orders 
    WHERE status = 'paid' 
    AND MONTH(created_at) = '$lastMonth' 
    AND YEAR(created_at) = '$currentYear'
");
$lastMonthRevenue = mysqli_fetch_assoc($lastMonthRevenueQuery)['total'] ?? 0;

// Calculate growth percentage
$salesGrowth = 0;
if ($lastMonthRevenue > 0) {
    $salesGrowth = (($monthlyRevenue - $lastMonthRevenue) / $lastMonthRevenue) * 100;
}

/* AMBIL DATA ORDER + ITEM + PRODUK */
$reportQ = mysqli_query($conn, "
    SELECT 
        o.id_order,
        o.total_harga,
        o.metode_pembayaran,
        o.bukti_tf,
        o.status,
        o.created_at,
        oi.qty,
        p.nama_buku,
        u.nama as nama_pembeli,
        u.email as email_pembeli
    FROM orders o
    JOIN order_details oi ON o.id_order = oi.id_order
    JOIN produk p ON oi.id_produk = p.id_produk
    JOIN users u ON o.id_pembeli = u.id_user
    ORDER BY o.created_at DESC
    LIMIT 10
");

/* AMBIL TOP PRODUCTS */
$topProductsQuery = mysqli_query($conn, "
    SELECT 
        p.nama_buku,
        SUM(oi.qty) as total_terjual,
        p.harga,
        COUNT(DISTINCT o.id_order) as total_order
    FROM order_details oi
    JOIN produk p ON oi.id_produk = p.id_produk
    JOIN orders o ON oi.id_order = o.id_order
    WHERE o.status = 'paid'
    GROUP BY p.id_produk
    ORDER BY total_terjual DESC
    LIMIT 5
");

/* AMBIL RECENT ACTIVITIES */
$recentActivitiesQuery = mysqli_query($conn, "
    SELECT 
        'order' as type,
        o.id_order,
        u.nama,
        o.total_harga,
        o.created_at,
        o.status
    FROM orders o
    JOIN users u ON o.id_pembeli = u.id_user
    UNION ALL
    SELECT 
        'user' as type,
        id_user,
        nama,
        0 as total_harga,
        created_at,
        'active' as status
    FROM users
    ORDER BY created_at DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        .brand-font {
            font-family: 'Playfair Display', serif;
        }
        body {
            background: linear-gradient(135deg, #fef3c7 0%, #fefce8 100%);
            min-height: 100vh;
        }
        .dashboard-card {
            background: linear-gradient(135deg, #ffffff 0%, #fefce8 100%);
            border: 1px solid #fde68a;
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.1);
            transition: all 0.3s ease;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 35px rgba(217, 119, 6, 0.15);
        }
        .stat-card {
            background: linear-gradient(135deg, #78350f 0%, #92400e 100%);
            box-shadow: 0 10px 30px rgba(120, 53, 15, 0.25);
        }
        .sidebar-card {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            border: 1px solid #334155;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        .status-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .status-shipped {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            color: white;
        }
        .status-delivered {
            background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%);
            color: white;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto p-4 md:p-6">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-10">
            <div class="mb-6 md:mb-0">
                <h1 class="text-4xl font-bold brand-font text-amber-900 mb-2">AKSARA JIWA</h1>
                <div class="flex items-center gap-2">
                    <div class="w-3 h-3 bg-amber-500 rounded-full animate-pulse"></div>
                    <p class="text-amber-700 font-medium">Dashboard Admin Bookstore & Coffee</p>
                </div>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="relative">
                    <div class="flex items-center space-x-3 bg-gradient-to-r from-slate-800 to-slate-900 px-5 py-3 rounded-2xl shadow-lg">
                        <div class="hidden md:block text-right">
                            <p class="font-medium text-white"><?= htmlspecialchars($admin_nama) ?></p>
                            <p class="text-sm text-amber-200">Admin Manager</p>
                        </div>
                        <div class="w-12 h-12 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center shadow">
                            <i class="fas fa-user-cog text-white text-lg"></i>
                        </div>
                    </div>
                    <div class="absolute -top-1 -right-1 w-6 h-6 bg-green-500 rounded-full border-2 border-white flex items-center justify-center">
                        <i class="fas fa-crown text-xs text-white"></i>
                    </div>
                </div>
                <a href="../auth/logout.php" class="bg-gradient-to-r from-red-500 to-red-600 text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5 shadow">
                    <i class="fas fa-sign-out-alt mr-2"></i>Keluar
                </a>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-8">
                <!-- Statistik Card -->
                <div class="stat-card rounded-3xl p-8 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-white/10 rounded-full -translate-y-16 translate-x-16"></div>
                    <div class="absolute bottom-0 left-0 w-24 h-24 bg-white/10 rounded-full translate-y-12 -translate-x-12"></div>
                    
                    <div class="relative z-10">
                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <h2 class="text-2xl font-bold mb-2">📊 Ringkasan Keuangan</h2>
                                <p class="text-amber-200/80">Statistik Real-time <?= date('F Y') ?></p>
                            </div>
                            <div class="bg-white/20 p-4 rounded-2xl backdrop-blur-sm">
                                <i class="fas fa-chart-line text-3xl text-amber-100"></i>
                            </div>
                        </div>
                        
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                            <div class="bg-white/10 p-6 rounded-2xl backdrop-blur-sm border border-white/20">
                                <p class="text-sm text-amber-200/80 mb-2">Total Pesanan</p>
                                <p class="text-3xl font-bold"><?= $totalOrders ?></p>
                                <div class="flex items-center gap-1 mt-2">
                                    <i class="fas fa-shopping-cart text-amber-300 text-sm"></i>
                                    <span class="text-xs text-amber-200"><?= $pendingOrders ?> pending</span>
                                </div>
                            </div>
                            <div class="bg-white/10 p-6 rounded-2xl backdrop-blur-sm border border-white/20">
                                <p class="text-sm text-amber-200/80 mb-2">Pendapatan Bulan Ini</p>
                                <p class="text-3xl font-bold">Rp <?= number_format($monthlyRevenue, 0, ',', '.') ?></p>
                                <div class="flex items-center gap-1 mt-2">
                                    <i class="fas <?= $salesGrowth >= 0 ? 'fa-arrow-up text-green-300' : 'fa-arrow-down text-red-300' ?> text-xs"></i>
                                    <span class="text-xs <?= $salesGrowth >= 0 ? 'text-green-300' : 'text-red-300' ?>">
                                        <?= number_format(abs($salesGrowth), 1) ?>%
                                    </span>
                                </div>
                            </div>
                            <div class="bg-white/10 p-6 rounded-2xl backdrop-blur-sm border border-white/20">
                                <p class="text-sm text-amber-200/80 mb-2">Pelanggan Aktif</p>
                                <p class="text-3xl font-bold"><?= $activeCustomers ?></p>
                                <div class="flex items-center gap-1 mt-2">
                                    <i class="fas fa-users text-amber-300 text-sm"></i>
                                    <span class="text-xs text-amber-200">Aktif</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <p class="text-5xl font-bold brand-font"><?= date('Y') ?></p>
                            <p class="text-amber-200/80 mt-2">Total Pendapatan: Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
                        </div>
                    </div>
                </div>

                <!-- Pesanan Table -->
                <div class="dashboard-card rounded-3xl p-8">
                    <div class="flex justify-between items-center mb-8">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">📋 Data Pesanan Terbaru</h3>
                            <p class="text-slate-600 mt-1">10 pesanan terakhir</p>
                        </div>
                        <a href="manage_orders.php" class="flex items-center gap-2 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-6 py-3 rounded-xl hover:shadow-lg transition-all duration-300 hover:-translate-y-0.5">
                            <i class="fas fa-list"></i>
                            Lihat Semua
                        </a>
                    </div>

                    <div class="overflow-x-auto rounded-2xl border border-amber-100">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-amber-50 to-yellow-50">
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Kode Pesanan</th>
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Pembeli</th>
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Status</th>
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Metode Bayar</th>
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Total</th>
                                    <th class="text-left py-5 px-6 text-slate-800 font-semibold">Tanggal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50">
                            <?php if (mysqli_num_rows($reportQ) == 0): ?>
                            <tr>
                                <td colspan="6" class="py-16 text-center">
                                    <div class="w-24 h-24 mx-auto mb-4 flex items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-yellow-50">
                                        <i class="fas fa-inbox text-4xl text-amber-400"></i>
                                    </div>
                                    <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum ada data pesanan</h3>
                                    <p class="text-slate-500 max-w-md mx-auto mb-6">Belum ada transaksi yang tercatat</p>
                                </td>
                            </tr>
                            <?php else: ?>
                            <?php 
                            $orderCount = 0;
                            mysqli_data_seek($reportQ, 0);
                            while ($r = mysqli_fetch_assoc($reportQ)) : 
                                $orderCount++;
                                $statusClass = 'status-' . $r['status'];
                            ?>
                            <tr class="hover:bg-amber-50/50 transition-colors duration-200 group">
                                <td class="py-5 px-6">
                                    <div class="flex items-center">
                                        <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white font-bold w-12 h-12 flex items-center justify-center rounded-xl mr-4 shadow">
                                            #<?= $r['id_order'] ?>
                                        </div>
                                        <div>
                                            <span class="font-semibold text-slate-800">Order #<?= $r['id_order'] ?></span>
                                            <div class="text-xs text-slate-500 mt-1"><?= htmlspecialchars($r['nama_buku']) ?></div>
                                        </div>
                                    </div>
                                </td>

                                <td class="py-5 px-6">
                                    <div>
                                        <p class="font-medium text-slate-800"><?= htmlspecialchars($r['nama_pembeli']) ?></p>
                                        <p class="text-xs text-slate-500"><?= htmlspecialchars($r['email_pembeli']) ?></p>
                                    </div>
                                </td>

                                <td class="py-5 px-6">
                                    <span class="status-badge <?= $statusClass ?>">
                                        <?= ucfirst($r['status']) ?>
                                    </span>
                                </td>

                                <td class="py-5 px-6">
                                    <span class="inline-flex items-center gap-2 bg-gradient-to-br from-green-50 to-emerald-100 text-green-800 px-4 py-2 rounded-full font-semibold">
                                        <i class="fas fa-credit-card text-sm"></i>
                                        <?= htmlspecialchars($r['metode_pembayaran']) ?>
                                    </span>
                                </td>

                                <td class="py-5 px-6">
                                    <div class="text-right">
                                        <p class="font-bold text-xl text-amber-700">Rp <?= number_format($r['total_harga'], 0, ',', '.') ?></p>
                                        <p class="text-xs text-slate-500"><?= $r['qty'] ?> item</p>
                                    </div>
                                </td>

                                <td class="py-5 px-6">
                                    <div class="text-sm text-slate-600">
                                        <?= date('d M Y', strtotime($r['created_at'])) ?>
                                        <div class="text-xs text-slate-400">
                                            <?= date('H:i', strtotime($r['created_at'])) ?>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="mt-8 p-6 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-2xl border border-amber-100">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-semibold text-slate-800">Menampilkan <?= $orderCount ?> pesanan terbaru</p>
                                <p class="text-sm text-slate-600">Total <?= $totalOrders ?> pesanan dalam sistem</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <a href="export_orders.php" class="flex items-center gap-2 text-amber-700 hover:text-amber-800 font-medium">
                                    <i class="fas fa-download"></i>
                                    Export Data
                                </a>
                                <button onclick="printReport()" class="flex items-center gap-2 bg-white text-slate-700 px-4 py-2 rounded-lg border border-amber-200 hover:shadow transition-all">
                                    <i class="fas fa-print"></i>
                                    Cetak Laporan
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Chart Section -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Top Products -->
                    <div class="dashboard-card rounded-3xl p-8">
                        <h3 class="text-2xl font-bold text-slate-800 mb-6">📈 Buku Terlaris</h3>
                        <div class="space-y-4">
                            <?php if (mysqli_num_rows($topProductsQuery) == 0): ?>
                                <p class="text-slate-500 text-center py-4">Belum ada data penjualan</p>
                            <?php else: ?>
                            <?php while ($product = mysqli_fetch_assoc($topProductsQuery)): ?>
                            <div class="flex items-center justify-between p-4 bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl border border-slate-200">
                                <div class="flex items-center gap-3">
                                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-amber-600 rounded-lg flex items-center justify-center">
                                        <i class="fas fa-book text-white"></i>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($product['nama_buku']) ?></p>
                                        <p class="text-sm text-slate-500">Rp <?= number_format($product['harga'], 0, ',', '.') ?></p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-bold text-lg text-amber-700"><?= $product['total_terjual'] ?> terjual</p>
                                    <p class="text-xs text-slate-500"><?= $product['total_order'] ?> order</p>
                                </div>
                            </div>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Recent Activities -->
                    <div class="dashboard-card rounded-3xl p-8">
                        <h3 class="text-2xl font-bold text-slate-800 mb-6">🔄 Aktivitas Terbaru</h3>
                        <div class="space-y-4">
                            <?php if (mysqli_num_rows($recentActivitiesQuery) == 0): ?>
                                <p class="text-slate-500 text-center py-4">Belum ada aktivitas</p>
                            <?php else: ?>
                            <?php while ($activity = mysqli_fetch_assoc($recentActivitiesQuery)): ?>
                            <div class="flex items-center gap-3 p-4 bg-gradient-to-r from-blue-50 to-cyan-50 rounded-xl border border-blue-200">
                                <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-cyan-500 flex items-center justify-center">
                                    <?php if ($activity['type'] == 'order'): ?>
                                        <i class="fas fa-shopping-bag text-white"></i>
                                    <?php else: ?>
                                        <i class="fas fa-user-plus text-white"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <p class="font-semibold text-slate-800">
                                        <?php if ($activity['type'] == 'order'): ?>
                                            Order #<?= $activity['id_order'] ?> oleh <?= htmlspecialchars($activity['nama']) ?>
                                        <?php else: ?>
                                            User baru: <?= htmlspecialchars($activity['nama']) ?>
                                        <?php endif; ?>
                                    </p>
                                    <p class="text-xs text-slate-500">
                                        <?= date('d M H:i', strtotime($activity['created_at'])) ?>
                                    </p>
                                </div>
                                <?php if ($activity['type'] == 'order' && $activity['total_harga'] > 0): ?>
                                    <span class="font-bold text-amber-700">
                                        Rp <?= number_format($activity['total_harga'], 0, ',', '.') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <?php endwhile; ?>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-8">
                <!-- Quick Stats -->
                <div class="sidebar-card rounded-3xl p-8 text-white">
                    <h3 class="text-2xl font-bold mb-6 flex items-center gap-2">
                        <i class="fas fa-tachometer-alt"></i>
                        Statistik Cepat
                    </h3>
                    <div class="space-y-4">
                        <div class="bg-white/10 p-5 rounded-2xl backdrop-blur-sm border border-white/20">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm opacity-90">Pendapatan Hari Ini</p>
                                <i class="fas fa-coins text-amber-300"></i>
                            </div>
                            <?php
                            $today = date('Y-m-d');
                            $todayRevenueQuery = mysqli_query($conn, "
                                SELECT SUM(total_harga) as total 
                                FROM orders 
                                WHERE status = 'paid' 
                                AND DATE(created_at) = '$today'
                            ");
                            $todayRevenue = mysqli_fetch_assoc($todayRevenueQuery)['total'] ?? 0;
                            ?>
                            <p class="text-3xl font-bold">Rp <?= number_format($todayRevenue, 0, ',', '.') ?></p>
                        </div>
                        
                        <div class="bg-white/10 p-5 rounded-2xl backdrop-blur-sm border border-white/20">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm opacity-90">Pesanan Hari Ini</p>
                                <i class="fas fa-shopping-cart text-amber-300"></i>
                            </div>
                            <?php
                            $todayOrdersQuery = mysqli_query($conn, "
                                SELECT COUNT(*) as total 
                                FROM orders 
                                WHERE DATE(created_at) = '$today'
                            ");
                            $todayOrders = mysqli_fetch_assoc($todayOrdersQuery)['total'] ?? 0;
                            ?>
                            <p class="text-3xl font-bold"><?= $todayOrders ?></p>
                            <div class="flex items-center gap-1 mt-2">
                                <i class="fas fa-clock text-amber-300 text-xs"></i>
                                <span class="text-xs text-amber-200">Hari ini</span>
                            </div>
                        </div>
                        
                        <div class="bg-white/10 p-5 rounded-2xl backdrop-blur-sm border border-white/20">
                            <div class="flex items-center justify-between mb-3">
                                <p class="text-sm opacity-90">Produk Tersedia</p>
                                <i class="fas fa-boxes text-amber-300"></i>
                            </div>
                            <?php
                            $totalProductsQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM produk");
                            $totalProducts = mysqli_fetch_assoc($totalProductsQuery)['total'] ?? 0;
                            ?>
                            <p class="text-3xl font-bold"><?= $totalProducts ?></p>
                            <div class="flex items-center gap-1 mt-2">
                                <i class="fas fa-book text-amber-300 text-xs"></i>
                                <span class="text-xs text-amber-200">Buku tersedia</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Admin Tools -->
                <div class="dashboard-card rounded-3xl p-8">
                    <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-tools text-amber-600"></i>
                        Tools Admin
                    </h3>
                    <div class="grid grid-cols-2 gap-3">
                        <a href="manage_products.php" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 border border-blue-200 hover:shadow-lg transition-all duration-300 group">
                            <i class="fas fa-box text-blue-600 text-2xl mb-2"></i>
                            <span class="font-semibold text-slate-800 text-sm">Produk</span>
                        </a>
                        <a href="manage_users.php" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-green-50 to-green-100 border border-green-200 hover:shadow-lg transition-all duration-300 group">
                            <i class="fas fa-users text-green-600 text-2xl mb-2"></i>
                            <span class="font-semibold text-slate-800 text-sm">Users</span>
                        </a>
                        <a href="manage_orders.php" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-purple-50 to-purple-100 border border-purple-200 hover:shadow-lg transition-all duration-300 group">
                            <i class="fas fa-shopping-cart text-purple-600 text-2xl mb-2"></i>
                            <span class="font-semibold text-slate-800 text-sm">Orders</span>
                        </a>
                        <a href="reports.php" class="flex flex-col items-center justify-center p-4 rounded-xl bg-gradient-to-br from-amber-50 to-yellow-100 border border-amber-200 hover:shadow-lg transition-all duration-300 group">
                            <i class="fas fa-chart-bar text-amber-600 text-2xl mb-2"></i>
                            <span class="font-semibold text-slate-800 text-sm">Reports</span>
                        </a>
                    </div>
                </div>

                <!-- System Info -->
                <div class="dashboard-card rounded-3xl p-8">
                    <h3 class="text-2xl font-bold text-slate-800 mb-6 flex items-center gap-2">
                        <i class="fas fa-info-circle text-amber-600"></i>
                        Info Sistem
                    </h3>
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl">
                            <span class="text-slate-700">Server Time</span>
                            <span class="font-semibold text-slate-800"><?= date('H:i:s') ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl">
                            <span class="text-slate-700">PHP Version</span>
                            <span class="font-semibold text-slate-800"><?= phpversion() ?></span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-gradient-to-r from-slate-50 to-slate-100 rounded-xl">
                            <span class="text-slate-700">Database</span>
                            <span class="font-semibold text-slate-800">MySQL</span>
                        </div>
                    </div>
                    <div class="mt-6 p-4 bg-gradient-to-r from-green-50 to-emerald-100 rounded-xl border border-green-200">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-check-circle text-green-600"></i>
                            <span class="font-semibold text-green-800">Sistem Aktif</span>
                        </div>
                        <p class="text-sm text-green-700 mt-1">Semua layanan berjalan normal</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-12 pt-8 border-t border-amber-100">
            <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                <div>
                    <div class="flex items-center gap-2 mb-2">
                        <div class="w-3 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                        <div class="w-3 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                        <div class="w-3 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                    </div>
                    <p class="text-slate-600 text-sm">© <?= date('Y'); ?> <span class="font-semibold text-amber-700 brand-font">Aksara Jiwa</span>. Bookstore & Coffee.</p>
                    <p class="text-slate-500 text-xs mt-1">Dashboard Admin v2.1 | Total Pesanan: <?= $totalOrders ?> | Pendapatan: Rp <?= number_format($totalRevenue, 0, ',', '.') ?></p>
                </div>
                <div class="flex items-center gap-4 text-sm">
                    <span class="text-slate-600">Terakhir diupdate: <?= date('d M Y H:i:s') ?></span>
                    <div class="flex items-center gap-2 text-green-600">
                        <i class="fas fa-circle text-xs animate-pulse"></i>
                        <span>Online</span>
                    </div>
                </div>
            </div>
        </footer>
    </div>

    <script>
        // Print Report Function
        function printReport() {
            window.print();
        }
        
        // Auto-refresh every 60 seconds
        setInterval(() => {
            location.reload();
        }, 60000);
        
        // Chart initialization
        document.addEventListener('DOMContentLoaded', function() {
            // Sales Chart
            const salesCtx = document.getElementById('salesChart');
            if (salesCtx) {
                new Chart(salesCtx, {
                    type: 'line',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                        datasets: [{
                            label: 'Penjualan',
                            data: [12000000, 19000000, 15000000, 25000000, 22000000, 30000000],
                            borderColor: '#d97706',
                            backgroundColor: 'rgba(217, 119, 6, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                display: false
                            }
                        }
                    }
                });
            }
            
            // Add hover effects
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-2px)';
                    this.style.boxShadow = '0 4px 12px rgba(217, 119, 6, 0.1)';
                });
                row.addEventListener('mouseleave', function() {
                    this.style.transform = '';
                    this.style.boxShadow = '';
                });
            });
            
            // Update time every second
            function updateTime() {
                const now = new Date();
                const timeString = now.toLocaleTimeString('id-ID');
                const dateString = now.toLocaleDateString('id-ID', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
                
                const timeElements = document.querySelectorAll('.current-time');
                timeElements.forEach(el => {
                    el.textContent = timeString;
                });
                
                const dateElements = document.querySelectorAll('.current-date');
                dateElements.forEach(el => {
                    el.textContent = dateString;
                });
            }
            
            setInterval(updateTime, 1000);
            updateTime();
        });
    </script>
</body>
</html>