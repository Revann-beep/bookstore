<?php
session_start();
require '../auth/connection.php';

// CEK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}



// KONFIGURASI PAGINATION
$limit = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$start = ($page - 1) * $limit;

// FILTER BULAN TAHUN
$bulan = isset($_GET['bulan']) ? $_GET['bulan'] : date('m');
$tahun = isset($_GET['tahun']) ? $_GET['tahun'] : date('Y');
$filter = "WHERE MONTH(t.created_at)='$bulan' AND YEAR(t.created_at)='$tahun'";

// DOWNLOAD CSV
if (isset($_GET['download']) && $_GET['download'] == 'csv') {
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="laporan.csv"');
    
    $output = fopen("php://output", "w");
    fputcsv($output, ['Kode Pesanan','Nama Buku','Qty','Bukti TF','Metode Pembayaran','Total Modal','Total Penjualan','Total Keuntungan']);
    
    $sql = "SELECT t.kode_pesanan, td.nama_buku, td.qty, t.bukti_tf, t.metode_pembayaran, 
                   td.subtotal_modal AS total_modal, td.subtotal_penjualan AS total_penjualan, 
                   (td.subtotal_penjualan - td.subtotal_modal) AS total_keuntungan
            FROM orders t
            JOIN order_details td ON t.id_order = td.id_order
            $filter";
    
    $query = mysqli_query($conn, $sql);
    while ($row = mysqli_fetch_assoc($query)) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// HITUNG TOTAL DATA
$totalQuery = mysqli_query($conn, "SELECT COUNT(*) as total FROM orders t JOIN order_details td ON t.id_order = td.id_order $filter");
$totalData = mysqli_fetch_assoc($totalQuery)['total'];
$totalPage = ceil($totalData / $limit);

// AMBIL DATA
$sql = "SELECT t.kode_pesanan, td.nama_buku, td.qty, t.bukti_tf, t.metode_pembayaran,
               td.subtotal_modal AS total_modal, td.subtotal_penjualan AS total_penjualan,
               (td.subtotal_penjualan - td.subtotal_modal) AS total_keuntungan
        FROM orders t
        JOIN order_details td ON t.id_order = td.id_order
        $filter
        LIMIT $start, $limit";
$data = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan | Aksara Jiwa</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
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
                    <h1 class="text-2xl font-bold text-gray-800">Laporan Penjualan</h1>
                    <p class="text-gray-600 mt-1">Analisis dan pantau performa penjualan Anda</p>
                </div>
                <div class="text-sm text-gray-500">
                    Periode: <?= date('F Y', strtotime($tahun.'-'.$bulan.'-01')) ?>
                </div>
            </div>
        </div>

        <!-- FILTER & ACTION -->
        <div class="bg-white rounded-xl shadow p-5 mb-6">
            <form method="GET" class="flex flex-wrap gap-4 items-center">
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Bulan:</label>
                    <select name="bulan" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <?php for ($m=1; $m<=12; $m++): ?>
                            <option value="<?= sprintf('%02d', $m) ?>" <?= $bulan == sprintf('%02d', $m) ? 'selected' : '' ?>>
                                <?= date('F', mktime(0,0,0,$m,10)) ?>
                            </option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <div class="flex items-center gap-2">
                    <label class="text-sm text-gray-600">Tahun:</label>
                    <select name="tahun" class="px-3 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                        <?php for ($y=2023; $y<=date('Y'); $y++): ?>
                            <option value="<?= $y ?>" <?= $tahun == $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                
                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition">
                    <i class="fas fa-filter mr-2"></i> Terapkan Filter
                </button>
                
                <div class="flex gap-2 ml-auto">
                    <a href="?download=csv&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                        <i class="fas fa-download"></i> Download CSV
                    </a>
                    <a href="grafik.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition flex items-center gap-2">
                        <i class="fas fa-chart-bar"></i> Lihat Grafik
                    </a>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-4 text-left font-semibold text-gray-700">Kode Pesanan</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Nama Buku</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Qty</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Bukti TF</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Metode Bayar</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Modal</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Penjualan</th>
                            <th class="p-4 text-left font-semibold text-gray-700">Keuntungan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (mysqli_num_rows($data) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($data)): ?>
                                <tr class="hover:bg-gray-50">
                                    <td class="p-4">
                                        <span class="font-medium text-gray-800"><?= htmlspecialchars($row['kode_pesanan']) ?></span>
                                    </td>
                                    <td class="p-4 text-gray-800"><?= htmlspecialchars($row['nama_buku']) ?></td>
                                    <td class="p-4">
                                        <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded-full text-sm">
                                            <?= $row['qty'] ?>
                                        </span>
                                    </td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($row['bukti_tf']) ?></td>
                                    <td class="p-4 text-gray-600"><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                                    <td class="p-4 text-gray-700">Rp <?= number_format($row['total_modal']) ?></td>
                                    <td class="p-4 text-gray-800 font-medium">Rp <?= number_format($row['total_penjualan']) ?></td>
                                    <td class="p-4">
                                        <span class="font-bold <?= $row['total_keuntungan'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                            Rp <?= number_format($row['total_keuntungan']) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    <i class="fas fa-inbox text-3xl mb-3"></i>
                                    <p>Tidak ada data laporan untuk periode ini</p>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PAGINATION -->
        <?php if ($totalPage > 1): ?>
            <div class="mt-6 flex items-center justify-between">
                <p class="text-sm text-gray-600">
                    Menampilkan <?= min($limit, mysqli_num_rows($data)) ?> dari <?= $totalData ?> data
                </p>
                
                <div class="flex gap-2">
                    <a href="?page=<?= max(1, $page-1) ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition <?= $page <= 1 ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        <i class="fas fa-chevron-left mr-1"></i> Previous
                    </a>
                    
                    <div class="flex items-center px-4 py-2 bg-indigo-100 text-indigo-700 rounded-lg font-medium">
                        Halaman <?= $page ?> dari <?= $totalPage ?>
                    </div>
                    
                    <a href="?page=<?= min($totalPage, $page+1) ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition <?= $page >= $totalPage ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        Next <i class="fas fa-chevron-right ml-1"></i>
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>