<?php
session_start();
require '../auth/connection.php';

// CEK LOGIN PEMBELI
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../index.php");
    exit;
}



$pembeli_id = $_SESSION['id_user'];
$pembeli_nama = $_SESSION['nama'];

// AMBIL TAHUN DAN BULAN DARI URL ATAU GUNAKAN BULAN SEKARANG
$selected_year = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$selected_month = isset($_GET['month']) 
    ? (int) $_GET['month'] 
    : (int) date('m');

// VALIDASI TAHUN DAN BULAN
$current_year = date('Y');
$current_month = date('m');

if ($selected_year < 2020 || $selected_year > $current_year + 1) {
    $selected_year = $current_year;
}
if ($selected_month < 1 || $selected_month > 12) {
    $selected_month = $current_month;
}

// AMBIL DATA LAPORAN PEMBELI BERDASARKAN FILTER
$reportQuery = mysqli_query($conn, "
    SELECT 
        o.id_order,
        p.nama_buku,
        oi.qty,
        o.bukti_tf,
        o.metode_pembayaran,
        o.total_harga,
        o.status,
        o.created_at
    FROM orders o
    JOIN order_details oi ON o.id_order = oi.id_order
    JOIN produk p ON oi.id_produk = p.id_produk
    WHERE o.id_pembeli = '$pembeli_id'
    AND YEAR(o.created_at) = '$selected_year'
    AND MONTH(o.created_at) = '$selected_month'
    ORDER BY o.created_at DESC
");

// HITUNG TOTAL PENGELUARAN BULAN INI
$totalQuery = mysqli_query($conn, "
    SELECT SUM(total_harga) as total 
    FROM orders 
    WHERE id_pembeli = '$pembeli_id' 
    AND status = 'paid'
    AND YEAR(created_at) = '$selected_year'
    AND MONTH(created_at) = '$selected_month'
");
$totalPembelian = mysqli_fetch_assoc($totalQuery)['total'] ?? 0;

// HITUNG JUMLAH TRANSAKSI
$countQuery = mysqli_query($conn, "
    SELECT COUNT(*) as count
    FROM orders 
    WHERE id_pembeli = '$pembeli_id'
    AND YEAR(created_at) = '$selected_year'
    AND MONTH(created_at) = '$selected_month'
");
$jumlahTransaksi = mysqli_fetch_assoc($countQuery)['count'] ?? 0;

// NAMA BULAN DALAM BAHASA INDONESIA
$bulan_indonesia = [
    1 => 'Januari',
    2 => 'Februari',
    3 => 'Maret',
    4 => 'April',
    5 => 'Mei',
    6 => 'Juni',
    7 => 'Juli',
    8 => 'Agustus',
    9 => 'September',
    10 => 'Oktober',
    11 => 'November',
    12 => 'Desember'
];

$nama_bulan_sekarang = $bulan_indonesia[$selected_month];

// GENERATE LIST TAHUN (2020 - TAHUN SEKARANG)
$tahun_options = [];
$start_year = 2020;
$end_year = $current_year;

for ($year = $end_year; $year >= $start_year; $year--) {
    $tahun_options[$year] = $year;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Pembelian - Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
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
        .report-card {
            background: white;
            border: 1px solid #fde68a;
            box-shadow: 0 8px 25px rgba(217, 119, 6, 0.1);
            border-radius: 20px;
        }
        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-paid {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        .status-pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
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
        .custom-select {
            appearance: none;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 0.5rem center;
            background-repeat: no-repeat;
            background-size: 1.5em 1.5em;
            padding-right: 2.5rem;
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="flex">
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl p-6 min-h-screen">
    <div class="mb-10">
        <h1 class="text-3xl font-bold text-amber-300 brand-font mb-1">AKSARA</h1>
        <h1 class="text-3xl font-bold text-amber-100 brand-font">JIWA</h1>
        <p class="text-slate-400 text-sm mt-2">Bookstore</p>
    </div>
    <nav class="space-y-2">
        <a href="dashboard_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
            </svg>
            Dashboard
        </a>
        <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
            </svg>
            Produk
        </a>
        <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Status
        </a>
        <a href="pesan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 5v8a2 2 0 01-2 2h-5l-5 4v-4H4a2 2 0 01-2-2V5a2 2 0 012-2h12a2 2 0 012 2zM7 8H5v2h2V8zm2 0h2v2H9V8zm6 0h-2v2h2V8z" clip-rule="evenodd" />
            </svg>
            Chat
        </a>
        <!-- Help Section Added Here -->
        <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
            </svg>
            Help
        </a>
        <a href="report.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg hover:shadow-amber-200/30 transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
            </svg>
            Laporan
        </a>
        <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
            </svg>
            My Account
        </a>
        <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-red-900/30 hover:text-red-200 transition-all duration-300 mt-8">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
            </svg>
            Sign Out
        </a>
    </nav>
</aside>

        <!-- Main Content -->
        <div class="flex-1">
            <div class="container mx-auto p-4 md:p-6 max-w-6xl">
                <!-- Header -->
                <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
                    <div class="mb-6 md:mb-0">
                        <h1 class="text-3xl font-bold brand-font text-amber-900 mb-2">LAPORAN PEMBELIAN</h1>
                        <div class="flex items-center gap-2">
                            <i class="fas fa-user text-amber-600"></i>
                            <p class="text-amber-700 font-medium"><?= htmlspecialchars($pembeli_nama) ?></p>
                            <span class="px-2 py-1 bg-amber-100 text-amber-800 text-xs rounded-full">Pembeli</span>
                        </div>
                    </div>
                    
                    <div class="flex items-center space-x-4">
                        <a href="../index.php" class="bg-gradient-to-r from-amber-500 to-amber-600 text-white px-4 py-2 rounded-xl hover:shadow-lg transition-all">
                            <i class="fas fa-home mr-2"></i>Beranda
                        </a>
                    </div>
                </header>

                <!-- Filter Dropdown Tahun dan Bulan -->
                <div class="report-card p-6 mb-6">
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div>
                            <h2 class="text-xl font-bold text-slate-800 mb-1">Filter Periode Laporan</h2>
                            <p class="text-slate-600">Pilih tahun dan bulan untuk melihat laporan</p>
                        </div>
                        
                        <div class="flex items-center space-x-4 bg-gradient-to-r from-amber-500 to-amber-600 px-4 py-2 rounded-xl text-white">
                            <i class="fas fa-calendar-alt"></i>
                            <span><?= $nama_bulan_sekarang ?> <?= $selected_year ?></span>
                        </div>
                    </div>

                    <!-- Form Filter Dropdown -->
                    <form method="GET" action="" class="flex flex-col md:flex-row gap-4">
                        <!-- Dropdown Tahun -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-calendar mr-2"></i>Pilih Tahun
                            </label>
                            <div class="relative">
                                <select name="year" 
                                        onchange="this.form.submit()" 
                                        class="custom-select w-full px-4 py-3 bg-gradient-to-br from-slate-50 to-slate-100 border border-amber-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                    <?php foreach ($tahun_options as $year_value => $year_label): ?>
                                        <option value="<?= $year_value ?>" <?= $selected_year == $year_value ? 'selected' : '' ?>>
                                            <?= $year_label ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-slate-500"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Dropdown Bulan -->
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-slate-700 mb-2">
                                <i class="fas fa-calendar-day mr-2"></i>Pilih Bulan
                            </label>
                            <div class="relative">
                                <select name="month" 
                                        onchange="this.form.submit()" 
                                        class="custom-select w-full px-4 py-3 bg-gradient-to-br from-slate-50 to-slate-100 border border-amber-200 rounded-xl text-slate-700 focus:outline-none focus:ring-2 focus:ring-amber-500 focus:border-amber-500 transition-all">
                                    <?php foreach ($bulan_indonesia as $month_num => $month_name): ?>
                                        <option value="<?= $month_num ?>" <?= $selected_month == $month_num ? 'selected' : '' ?>>
                                            <?= $month_name ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="absolute right-4 top-1/2 transform -translate-y-1/2 pointer-events-none">
                                    <i class="fas fa-chevron-down text-slate-500"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Reset -->
                        <div class="flex items-end">
                            <a href="report.php" 
                               class="px-6 py-3 bg-gradient-to-br from-slate-700 to-slate-800 text-white rounded-xl hover:shadow-lg transition-all flex items-center gap-2">
                                <i class="fas fa-sync-alt"></i>
                                Reset
                            </a>
                        </div>
                    </form>
                </div>

                <!-- Ringkasan Statistik -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="report-card p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                                <i class="fas fa-shopping-cart text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-600 text-sm">Total Transaksi</p>
                                <p class="text-2xl font-bold text-amber-700"><?= $jumlahTransaksi ?></p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-amber-100">
                            <p class="text-xs text-slate-500">
                                <i class="fas fa-info-circle mr-1"></i>
                                <?= $nama_bulan_sekarang ?> <?= $selected_year ?>
                            </p>
                        </div>
                    </div>

                    <div class="report-card p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <i class="fas fa-money-bill-wave text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-600 text-sm">Total Pengeluaran</p>
                                <p class="text-2xl font-bold text-green-700">Rp <?= number_format($totalPembelian, 0, ',', '.') ?></p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-amber-100">
                            <p class="text-xs text-slate-500">
                                <i class="fas fa-calendar-check mr-1"></i>
                                Hanya transaksi berstatus "Paid"
                            </p>
                        </div>
                    </div>

                    <div class="report-card p-6">
                        <div class="flex items-center gap-4">
                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-chart-line text-white text-xl"></i>
                            </div>
                            <div>
                                <p class="text-slate-600 text-sm">Rata-rata Transaksi</p>
                                <p class="text-2xl font-bold text-blue-700">
                                    <?php 
                                    if ($jumlahTransaksi > 0 && $totalPembelian > 0) {
                                        echo 'Rp ' . number_format($totalPembelian / $jumlahTransaksi, 0, ',', '.');
                                    } else {
                                        echo 'Rp 0';
                                    }
                                    ?>
                                </p>
                            </div>
                        </div>
                        <div class="mt-4 pt-4 border-t border-amber-100">
                            <p class="text-xs text-slate-500">
                                <i class="fas fa-calculator mr-1"></i>
                                Total pengeluaran ÷ jumlah transaksi
                            </p>
                        </div>
                    </div>
                </div>

                <!-- Laporan Card -->
                <div class="report-card p-6 mb-8">
                    <div class="flex justify-between items-center mb-6">
                        <div>
                            <h2 class="text-2xl font-bold text-slate-800 mb-2">Riwayat Transaksi <?= $nama_bulan_sekarang ?> <?= $selected_year ?></h2>
                            <p class="text-slate-600">Semua pembelian yang telah Anda lakukan</p>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="printReport()" class="flex items-center gap-2 bg-gradient-to-r from-slate-700 to-slate-800 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all">
                                <i class="fas fa-print"></i>
                                Cetak
                            </button>
                            <a href="export_laporan.php?year=<?= $selected_year ?>&month=<?= $selected_month ?>" class="flex items-center gap-2 bg-gradient-to-r from-green-500 to-green-600 text-white px-4 py-2 rounded-lg hover:shadow-lg transition-all">
                                <i class="fas fa-download"></i>
                                Download
                            </a>
                        </div>
                    </div>

                    <!-- Tabel Laporan -->
                    <div class="overflow-x-auto rounded-xl border border-amber-100">
                        <table class="w-full">
                            <thead>
                                <tr class="bg-gradient-to-r from-amber-50 to-yellow-50">
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">Kode Pesanan</th>
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">Merek</th>
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">QTY</th>
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">Bukti</th>
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">Metode Pembayaran</th>
                                    <th class="text-left py-4 px-4 text-slate-800 font-semibold">Total Harga</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-amber-50">
                                <?php if (mysqli_num_rows($reportQuery) == 0): ?>
                                <tr>
                                    <td colspan="6" class="py-12 text-center">
                                        <div class="w-20 h-20 mx-auto mb-4 flex items-center justify-center rounded-full bg-gradient-to-br from-amber-50 to-yellow-50">
                                            <i class="fas fa-shopping-cart text-3xl text-amber-400"></i>
                                        </div>
                                        <h3 class="text-lg font-semibold text-slate-600 mb-2">Belum ada transaksi</h3>
                                        <p class="text-slate-500">Anda belum melakukan pembelian pada <?= $nama_bulan_sekarang ?> <?= $selected_year ?></p>
                                        <a href="halaman-pesanan.php" class="inline-block mt-4 bg-gradient-to-r from-amber-500 to-amber-600 text-white px-6 py-2 rounded-lg hover:shadow-lg transition-all">
                                            <i class="fas fa-shopping-bag mr-2"></i>Mulai Belanja
                                        </a>
                                    </td>
                                </tr>
                                <?php else: ?>
                                <?php while ($report = mysqli_fetch_assoc($reportQuery)): 
                                    $statusClass = 'status-' . $report['status'];
                                ?>
                                <tr class="hover:bg-amber-50/50 transition-colors duration-200">
                                    <!-- Kode Pesanan -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center">
                                            <div class="bg-gradient-to-br from-amber-500 to-amber-600 text-white font-bold w-10 h-10 flex items-center justify-center rounded-lg mr-3 shadow">
                                                <?= substr($report['id_order'], 0, 3) ?>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-slate-800"><?= $report['id_order'] ?></p>
                                                <div class="flex items-center gap-1 mt-1">
                                                    <span class="status-badge <?= $statusClass ?>">
                                                        <?= ucfirst($report['status']) ?>
                                                    </span>
                                                    <span class="text-xs text-slate-500">
                                                        <?= date('d/m/y', strtotime($report['created_at'])) ?>
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <!-- Merek -->
                                    <td class="py-4 px-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                                <i class="fas fa-book text-white"></i>
                                            </div>
                                            <span class="font-medium text-slate-800"><?= htmlspecialchars($report['nama_buku']) ?></span>
                                        </div>
                                    </td>
                                    
                                    <!-- QTY -->
                                    <td class="py-4 px-4">
                                        <div class="flex justify-center">
                                            <span class="bg-gradient-to-br from-amber-50 to-yellow-50 text-amber-700 font-bold px-3 py-1 rounded-lg">
                                                <?= $report['qty'] ?>
                                            </span>
                                        </div>
                                    </td>
                                    
                                    <!-- Bukti -->
                                    <td class="py-4 px-4">
                                        <?php if (!empty($report['bukti_tf'])): ?>
                                        <div class="relative group">
                                            <div class="w-12 h-12 rounded-lg overflow-hidden border-2 border-amber-200 cursor-pointer"
                                                 onclick="showImageModal('<?= $report['bukti_tf'] ?>')">
                                                <div class="w-full h-full bg-gradient-to-br from-green-100 to-emerald-100 flex items-center justify-center">
                                                    <i class="fas fa-receipt text-green-600"></i>
                                                </div>
                                            </div>
                                            <div class="absolute -top-8 left-1/2 transform -translate-x-1/2 bg-slate-800 text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity pointer-events-none">
                                                Klik untuk melihat
                                            </div>
                                        </div>
                                        <?php else: ?>
                                        <span class="text-slate-400 text-sm">-</span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <!-- Metode Pembayaran -->
                                    <td class="py-4 px-4">
                                        <span class="inline-flex items-center gap-2 px-3 py-1 rounded-lg font-medium
                                            <?= $report['metode_pembayaran'] == 'Transfer' ? 
                                               'bg-gradient-to-br from-green-50 to-emerald-100 text-green-800' : 
                                               'bg-gradient-to-br from-blue-50 to-cyan-100 text-blue-800' ?>">
                                            <i class="fas <?= $report['metode_pembayaran'] == 'Transfer' ? 'fa-university' : 'fa-money-bill-wave' ?>"></i>
                                            <?= htmlspecialchars($report['metode_pembayaran']) ?>
                                        </span>
                                    </td>
                                    
                                    <!-- Total Harga -->
                                    <td class="py-4 px-4">
                                        <div class="text-right">
                                            <p class="font-bold text-lg text-amber-700">Rp <?= number_format($report['total_harga'], 0, ',', '.') ?></p>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Ringkasan -->
                    <div class="mt-6 p-4 bg-gradient-to-r from-amber-50 to-yellow-50 rounded-xl border border-amber-100">
                        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
                            <div>
                                <p class="font-semibold text-slate-800">Menampilkan <?= $jumlahTransaksi ?> transaksi di <?= $nama_bulan_sekarang ?> <?= $selected_year ?></p>
                                <p class="text-sm text-slate-600">Riwayat pembelian Anda di Aksara Jiwa</p>
                            </div>
                            <div class="flex items-center gap-4">
                                <div class="text-right">
                                    <p class="text-sm text-slate-600">Total Pengeluaran</p>
                                    <p class="text-2xl font-bold text-amber-700">Rp <?= number_format($totalPembelian, 0, ',', '.') ?></p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Info Penting -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="report-card p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center">
                                <i class="fas fa-sync-alt text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Status Pesanan</h3>
                                <p class="text-sm text-slate-600">Cek status pemesanan</p>
                            </div>
                        </div>
                        <ul class="space-y-2 text-sm">
                            <li class="flex items-center gap-2">
                                <i class="fas fa-clock text-amber-500"></i>
                                <span>Pending: Menunggu verifikasi</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-green-500"></i>
                                <span>Paid: Pembayaran diterima</span>
                            </li>
                            <li class="flex items-center gap-2">
                                <i class="fas fa-shipping-fast text-blue-500"></i>
                                <span>Shipped: Sedang dikirim</span>
                            </li>
                        </ul>
                    </div>

                    <div class="report-card p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-question-circle text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Pertanyaan</h3>
                                <p class="text-sm text-slate-600">Butuh bantuan?</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mb-3">Jika ada masalah dengan pesanan atau pembayaran:</p>
                        <div class="flex flex-col gap-2">
                            <a href="mailto:admin@aksarajiwa.com" class="text-blue-600 hover:text-blue-800 text-sm">
                                <i class="fas fa-envelope mr-2"></i>Email: admin@aksarajiwa.com
                            </a>
                            <a href="https://wa.me/6281234567890" class="text-green-600 hover:text-green-800 text-sm">
                                <i class="fab fa-whatsapp mr-2"></i>WhatsApp: 0812-3456-7890
                            </a>
                        </div>
                    </div>

                    <div class="report-card p-6">
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                                <i class="fas fa-info-circle text-white"></i>
                            </div>
                            <div>
                                <h3 class="font-bold text-slate-800">Informasi</h3>
                                <p class="text-sm text-slate-600">Tentang laporan</p>
                            </div>
                        </div>
                        <p class="text-sm text-slate-600 mb-3">Laporan ini berisi:</p>
                        <ul class="text-sm text-slate-600 space-y-1">
                            <li>• Semua transaksi pembelian Anda</li>
                            <li>• Status setiap pesanan</li>
                            <li>• Bukti pembayaran yang diupload</li>
                            <li>• Riwayat pengeluaran total</li>
                        </ul>
                    </div>
                </div>

                <!-- Footer -->
                <footer class="mt-8 pt-6 border-t border-amber-100 text-center">
                    <p class="text-slate-600 text-sm">
                        © <?= date('Y'); ?> <span class="font-semibold text-amber-700 brand-font">Aksara Jiwa</span>. Bookstore .
                    </p>
                    <p class="text-slate-500 text-xs mt-1">
                        Laporan pembelian untuk <?= htmlspecialchars($pembeli_nama) ?> | Periode: <?= $nama_bulan_sekarang ?> <?= $selected_year ?>
                    </p>
                </footer>
            </div>
        </div>
    </div>

    <!-- Modal untuk menampilkan gambar bukti transfer -->
    <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 z-50 hidden items-center justify-center p-4">
        <div class="bg-white rounded-xl max-w-2xl w-full overflow-hidden">
            <div class="flex justify-between items-center p-4 border-b">
                <h3 class="text-lg font-bold text-slate-800">Bukti Transfer</h3>
                <button onclick="closeImageModal()" class="text-slate-400 hover:text-slate-600">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <div class="p-4">
                <img id="modalImage" src="" alt="Bukti Transfer" class="w-full h-auto rounded-lg max-h-[60vh] object-contain">
            </div>
            <div class="p-4 border-t bg-slate-50">
                <div class="flex justify-end gap-2">
                    <button onclick="closeImageModal()" class="px-4 py-2 text-slate-700 hover:text-slate-900 font-medium text-sm">
                        Tutup
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
    // Fungsi untuk menampilkan modal gambar
    function showImageModal(imageName) {
        const modal = document.getElementById('imageModal');
        const modalImage = document.getElementById('modalImage');
        
        modalImage.src = '../uploads/bukti_tf/' + imageName;
        
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    // Fungsi untuk menutup modal gambar
    function closeImageModal() {
        const modal = document.getElementById('imageModal');
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }

    // Fungsi untuk cetak laporan
    function printReport() {
        window.print();
    }

    // Tutup modal dengan ESC
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeImageModal();
        }
    });

    // Highlight row saat hover
    document.addEventListener('DOMContentLoaded', function() {
        const tableRows = document.querySelectorAll('tbody tr');
        tableRows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#fef3c7';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
    </script>
</body>
</html>