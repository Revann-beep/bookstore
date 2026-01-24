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
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Grafik Laporan | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body class="bg-gray-50">
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
                    <p class="text-xs text-gray-500">Analytics Dashboard</p>
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
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
                    <i class="fas fa-store w-5"></i> Data Penjual
                </a>
                <a href="grafik.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
                    <i class="fas fa-chart-bar w-5"></i> Grafik
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
    <main class="flex-1 ml-64 p-6 overflow-y-auto h-screen">
        <!-- HEADER -->
        <div class="mb-8">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Analisis Grafik</h1>
                    <p class="text-gray-600 mt-1">Visualisasi performa penjualan bulan <?= date('F', mktime(0, 0, 0, $bulan, 10)) ?> <?= $tahun ?></p>
                </div>
                <a href="laporan.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                   class="px-4 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                    <i class="fas fa-table"></i> Tabel Laporan
                </a>
            </div>
        </div>

        <!-- FILTER -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <form method="GET" class="flex items-center gap-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bulan</label>
                    <select name="bulan" class="w-40 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= sprintf('%02d', $m) ?>" <?= $bulan == sprintf('%02d', $m) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm text-gray-600 mb-1">Tahun</label>
                    <select name="tahun" class="w-32 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <?php for ($y=2023; $y<=date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="submit" class="mt-6 px-5 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition flex items-center gap-2">
                    <i class="fas fa-filter"></i> Terapkan
                </button>
            </form>
        </div>

        <!-- STATISTIK BULANAN -->
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-gradient-to-r from-blue-500 to-blue-600 text-white p-5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Transaksi</p>
                        <p class="text-2xl font-bold mt-1"><?= $stat['total_transaksi'] ?? 0 ?></p>
                    </div>
                    <i class="fas fa-shopping-cart text-xl opacity-80"></i>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-xl shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Penjualan</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">Rp <?= number_format($stat['total_penjualan_bulan'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-money-bill-wave text-xl text-green-500"></i>
                </div>
            </div>
            
            <div class="bg-white p-5 rounded-xl shadow">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm text-gray-600">Total Modal</p>
                        <p class="text-2xl font-bold text-gray-800 mt-1">Rp <?= number_format($stat['total_modal_bulan'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-calculator text-xl text-purple-500"></i>
                </div>
            </div>
            
            <div class="bg-gradient-to-r from-green-500 to-green-600 text-white p-5 rounded-xl">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm opacity-90">Total Keuntungan</p>
                        <p class="text-2xl font-bold mt-1">Rp <?= number_format($stat['total_keuntungan_bulan'] ?? 0) ?></p>
                    </div>
                    <i class="fas fa-chart-line text-xl opacity-80"></i>
                </div>
            </div>
        </div>

        <!-- GRAFIK UTAMA -->
        <div class="bg-white rounded-xl shadow p-6 mb-6">
            <h2 class="text-lg font-bold text-gray-800 mb-4">Perkembangan Penjualan Harian</h2>
            <div class="h-80">
                <canvas id="chartPenjualan"></canvas>
            </div>
        </div>

        <!-- GRAFIK PERBANDINGAN -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Perbandingan Modal vs Penjualan</h2>
                <div class="h-64">
                    <canvas id="chartPerbandingan"></canvas>
                </div>
            </div>
            
            <div class="bg-white rounded-xl shadow p-6">
                <h2 class="text-lg font-bold text-gray-800 mb-4">Keuntungan Harian</h2>
                <div class="h-64">
                    <canvas id="chartKeuntungan"></canvas>
                </div>
            </div>
        </div>

        <?php if (empty($labels)): ?>
            <div class="mt-6 bg-white rounded-xl shadow p-8 text-center">
                <i class="fas fa-chart-line text-4xl text-gray-300 mb-4"></i>
                <h3 class="text-xl font-semibold text-gray-700 mb-2">Tidak ada data</h3>
                <p class="text-gray-500">Belum ada transaksi pada periode ini</p>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
    <?php if (!empty($labels)): ?>
    // Grafik Utama - Line Chart
    const ctx1 = document.getElementById('chartPenjualan').getContext('2d');
    new Chart(ctx1, {
        type: 'line',
        data: {
            labels: <?= json_encode($labels) ?>,
            datasets: [{
                label: 'Penjualan Harian',
                data: <?= json_encode($dataPenjualan) ?>,
                borderColor: '#4f46e5',
                backgroundColor: 'rgba(79, 70, 229, 0.1)',
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
                    backgroundColor: '#8b5cf6',
                    borderColor: '#7c3aed',
                    borderWidth: 1
                },
                {
                    label: 'Penjualan',
                    data: <?= json_encode($dataPenjualan) ?>,
                    backgroundColor: '#10b981',
                    borderColor: '#059669',
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
                borderColor: (context) => {
                    const value = context.dataset.data[context.dataIndex];
                    return value >= 0 ? '#10b981' : '#ef4444';
                },
                backgroundColor: (context) => {
                    const value = context.dataset.data[context.dataIndex];
                    return value >= 0 ? 'rgba(16, 185, 129, 0.2)' : 'rgba(239, 68, 68, 0.2)';
                },
                borderWidth: 2,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: (context) => {
                    const value = context.dataset.data[context.dataIndex];
                    return value >= 0 ? '#10b981' : '#ef4444';
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
    <?php endif; ?>
</script>
</body>
</html>