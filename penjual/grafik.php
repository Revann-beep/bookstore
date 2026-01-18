<?php
session_start();
require '../auth/connection.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');

// Query grafik (total penjualan per hari)
$sql = "
    SELECT
        DAY(t.created_at) AS hari,
        SUM(td.subtotal_penjualan) AS total_penjualan,
        SUM(td.subtotal_modal) AS total_modal,
        SUM(td.subtotal_penjualan - td.subtotal_modal) AS total_keuntungan
    FROM orders t
    JOIN order_details td ON t.id_order = td.id_order
    WHERE MONTH(t.created_at)='$bulan' AND YEAR(t.created_at)='$tahun'
    GROUP BY DAY(t.created_at)
    ORDER BY DAY(t.created_at)
";
$result = mysqli_query($conn, $sql);

$labels = [];
$dataPenjualan = [];
$dataModal = [];
$dataKeuntungan = [];

while ($row = mysqli_fetch_assoc($result)) {
    $labels[] = $row['hari'];
    $dataPenjualan[] = $row['total_penjualan'];
    $dataModal[] = $row['total_modal'];
    $dataKeuntungan[] = $row['total_keuntungan'];
}

// Statistik bulanan
$statSql = "
    SELECT 
        COUNT(DISTINCT t.id_order) as total_transaksi,
        SUM(td.subtotal_penjualan) as total_penjualan_bulan,
        SUM(td.subtotal_modal) as total_modal_bulan,
        SUM(td.subtotal_penjualan - td.subtotal_modal) as total_keuntungan_bulan,
        AVG(td.subtotal_penjualan - td.subtotal_modal) as rata_keuntungan
    FROM orders t
    JOIN order_details td ON t.id_order = td.id_order
    WHERE MONTH(t.created_at)='$bulan' AND YEAR(t.created_at)='$tahun'
";
$statResult = mysqli_query($conn, $statSql);
$stat = mysqli_fetch_assoc($statResult);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Laporan - Penjual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        .active-link { background-color: #e6fffa; color: #0d9488; font-weight: 600; }
        .hover-effect:hover { background-color: #f0fdfa; transform: translateX(5px); transition: all 0.2s; }
        .stat-card { transition: transform 0.3s, box-shadow 0.3s; }
        .stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1); }
    </style>
</head>

<body class="flex min-h-screen bg-gray-50">
    <!-- SIDEBAR -->
    <aside class="w-64 bg-white border-r border-gray-200 flex flex-col">
        <div class="p-6 border-b">
            <h2 class="text-lg font-semibold text-gray-800">📚 Toko Buku</h2>
            <p class="text-sm text-gray-500 mt-1">Penjual Dashboard</p>
        </div>

        <nav class="flex-1 p-4 space-y-1">
            <a href="dashboard.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                📊 Dashboard
            </a>
            
            <a href="produk.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                📦 Produk
            </a>
            
            <div class="mt-4 mb-2">
                <p class="text-xs font-semibold text-gray-500 px-4 mb-2">MANAJEMEN ORDER</p>
                <a href="approve.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                    ✅ Approve Order
                </a>
                <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                    📑 Laporan Tabel
                </a>
                <a href="grafik.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" class="flex items-center gap-3 px-4 py-3 rounded-lg active-link">
                    📈 Laporan Grafik
                </a>
                <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                    💬 Chat
                </a>
            </div>
            
            <div class="mt-6 mb-2">
                <p class="text-xs font-semibold text-gray-500 px-4 mb-2">AKUN</p>
                <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                    👤 My Account
                </a>
                <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-red-500">
                    🔒 Sign Out
                </a>
            </div>
        </nav>

        <div class="p-4 border-t">
            <a href="help.php" class="flex items-center gap-3 text-gray-500 hover:text-teal-600">
                ❓ Help & Support
            </a>
        </div>
    </aside>

    <!-- MAIN CONTENT -->
    <main class="flex-1 p-6">
        <!-- HEADER -->
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-800 mb-2">📈 Grafik Penjualan Bulanan</h1>
            <p class="text-gray-600">Analisis performa penjualan bulan <?= date('F', mktime(0, 0, 0, $bulan, 10)) ?> <?= $tahun ?></p>
        </div>

        <!-- FILTER -->
        <div class="bg-white rounded-xl shadow-sm p-4 mb-6">
            <form method="GET" class="flex flex-wrap gap-3 items-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Bulan:</label>
                    <select name="bulan" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= sprintf('%02d', $m) ?>" <?= $bulan == sprintf('%02d', $m) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Tahun:</label>
                    <select name="tahun" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                        <?php for ($y=2023; $y<=date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="submit" class="px-4 py-2 bg-teal-600 text-white rounded-lg hover:bg-teal-700 transition">
                    Terapkan Filter
                </button>
                
                <a href="laporan.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                   class="ml-auto px-4 py-2 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition flex items-center gap-2">
                    ← Kembali ke Tabel
                </a>
            </form>
        </div>

        <!-- STATISTIK BULANAN -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border-l-4 border-blue-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Transaksi</p>
                        <p class="text-2xl font-bold text-gray-800"><?= $stat['total_transaksi'] ?? 0 ?></p>
                    </div>
                    <div class="p-3 bg-blue-100 rounded-lg">
                        <span class="text-blue-600 text-xl">🛒</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border-l-4 border-green-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Penjualan</p>
                        <p class="text-2xl font-bold text-gray-800">Rp <?= number_format($stat['total_penjualan_bulan'] ?? 0) ?></p>
                    </div>
                    <div class="p-3 bg-green-100 rounded-lg">
                        <span class="text-green-600 text-xl">💰</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border-l-4 border-purple-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Modal</p>
                        <p class="text-2xl font-bold text-gray-800">Rp <?= number_format($stat['total_modal_bulan'] ?? 0) ?></p>
                    </div>
                    <div class="p-3 bg-purple-100 rounded-lg">
                        <span class="text-purple-600 text-xl">📊</span>
                    </div>
                </div>
            </div>
            
            <div class="stat-card bg-white rounded-xl shadow-sm p-5 border-l-4 border-teal-500">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-500">Total Keuntungan</p>
                        <p class="text-2xl font-bold <?= ($stat['total_keuntungan_bulan'] ?? 0) >= 0 ? 'text-teal-600' : 'text-red-600' ?>">
                            Rp <?= number_format($stat['total_keuntungan_bulan'] ?? 0) ?>
                        </p>
                    </div>
                    <div class="p-3 bg-teal-100 rounded-lg">
                        <span class="text-teal-600 text-xl">📈</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- GRAFIK UTAMA -->
        <div class="bg-white rounded-xl shadow-sm p-6 mb-6">
            <h2 class="text-lg font-semibold text-gray-800 mb-4">Perkembangan Penjualan Harian</h2>
            <div class="h-80">
                <canvas id="chartPenjualan"></canvas>
            </div>
        </div>

        <!-- GRAFIK PERBANDINGAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Perbandingan Modal vs Penjualan</h2>
                <div class="h-64">
                    <canvas id="chartPerbandingan"></canvas>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow-sm p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Keuntungan Harian</h2>
                <div class="h-64">
                    <canvas id="chartKeuntungan"></canvas>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Grafik Utama - Line Chart
        const ctx1 = document.getElementById('chartPenjualan').getContext('2d');
        new Chart(ctx1, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Penjualan Harian',
                    data: <?= json_encode($dataPenjualan) ?>,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Grafik Perbandingan - Bar Chart
        const ctx2 = document.getElementById('chartPerbandingan').getContext('2d');
        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [
                    {
                        label: 'Modal',
                        data: <?= json_encode($dataModal) ?>,
                        backgroundColor: 'rgba(139, 92, 246, 0.7)',
                        borderColor: '#8b5cf6',
                        borderWidth: 1
                    },
                    {
                        label: 'Penjualan',
                        data: <?= json_encode($dataPenjualan) ?>,
                        backgroundColor: 'rgba(16, 185, 129, 0.7)',
                        borderColor: '#10b981',
                        borderWidth: 1
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': Rp ' + context.raw.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Grafik Keuntungan - Line Chart
        const ctx3 = document.getElementById('chartKeuntungan').getContext('2d');
        new Chart(ctx3, {
            type: 'line',
            data: {
                labels: <?= json_encode($labels) ?>,
                datasets: [{
                    label: 'Keuntungan Harian',
                    data: <?= json_encode($dataKeuntungan) ?>,
                    borderColor: '#0d9488',
                    backgroundColor: (context) => {
                        const bgColor = [];
                        const data = context.chart.data.datasets[0].data;
                        data.forEach(value => {
                            bgColor.push(value >= 0 ? 'rgba(13, 148, 136, 0.2)' : 'rgba(239, 68, 68, 0.2)');
                        });
                        return bgColor;
                    },
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: (context) => {
                        const index = context.dataIndex;
                        const value = context.dataset.data[index];
                        return value >= 0 ? '#0d9488' : '#ef4444';
                    }
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const status = value >= 0 ? 'Untung' : 'Rugi';
                                return status + ': Rp ' + Math.abs(value).toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>