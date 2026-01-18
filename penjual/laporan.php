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
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Penjual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .active-link { background-color: #e6fffa; color: #0d9488; font-weight: 600; }
        .hover-effect:hover { background-color: #f0fdfa; transform: translateX(5px); transition: all 0.2s; }
        .table-row:hover { background-color: #f9fafb; }
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
                <a href="laporan.php" class="flex items-center gap-3 px-4 py-3 rounded-lg active-link">
                    📑 Laporan
                </a>
                <a href="chat.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
                    💬 Chat
                </a>
            </div>
            
            <div class="mt-6 mb-2">
                <p class="text-xs font-semibold text-gray-500 px-4 mb-2">AKUN</p>
                <a href="admin.php" class="flex items-center gap-3 px-4 py-3 rounded-lg hover-effect text-gray-700">
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
            <h1 class="text-2xl font-bold text-gray-800 mb-2">📑 Laporan Pembelian</h1>
            <p class="text-gray-600">Filter dan kelola laporan penjualan bulanan</p>
        </div>

        <!-- FILTER & ACTION -->
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
                
                <div class="flex gap-2 ml-auto">
                    <a href="?download=csv&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition flex items-center gap-2">
                        📥 Download CSV
                    </a>
                    <a href="grafik.php?bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center gap-2">
                        📈 Lihat Grafik
                    </a>
                </div>
            </form>
        </div>

        <!-- TABLE -->
        <div class="bg-white rounded-xl shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Kode Pesanan</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Nama Buku</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Qty</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Bukti TF</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Metode Bayar</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Modal</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Penjualan</th>
                            <th class="p-3 text-left text-sm font-semibold text-gray-700">Keuntungan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if (mysqli_num_rows($data) > 0): ?>
                            <?php while ($row = mysqli_fetch_assoc($data)): ?>
                                <tr class="table-row">
                                    <td class="p-3 text-sm text-gray-800"><?= htmlspecialchars($row['kode_pesanan']) ?></td>
                                    <td class="p-3 text-sm text-gray-800"><?= htmlspecialchars($row['nama_buku']) ?></td>
                                    <td class="p-3 text-sm text-gray-800"><?= $row['qty'] ?></td>
                                    <td class="p-3 text-sm text-gray-800"><?= htmlspecialchars($row['bukti_tf']) ?></td>
                                    <td class="p-3 text-sm text-gray-800"><?= htmlspecialchars($row['metode_pembayaran']) ?></td>
                                    <td class="p-3 text-sm text-gray-800">Rp <?= number_format($row['total_modal']) ?></td>
                                    <td class="p-3 text-sm text-gray-800">Rp <?= number_format($row['total_penjualan']) ?></td>
                                    <td class="p-3 text-sm font-semibold <?= $row['total_keuntungan'] >= 0 ? 'text-green-600' : 'text-red-600' ?>">
                                        Rp <?= number_format($row['total_keuntungan']) ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="p-8 text-center text-gray-500">
                                    Tidak ada data laporan untuk periode ini
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
                        ← Previous
                    </a>
                    
                    <div class="flex items-center px-4 py-2 bg-teal-50 text-teal-700 rounded-lg font-medium">
                        Halaman <?= $page ?> dari <?= $totalPage ?>
                    </div>
                    
                    <a href="?page=<?= min($totalPage, $page+1) ?>&bulan=<?= $bulan ?>&tahun=<?= $tahun ?>" 
                       class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition <?= $page >= $totalPage ? 'opacity-50 cursor-not-allowed' : '' ?>">
                        Next →
                    </a>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>