<?php
require '../auth/connection.php';

$id_order = $_GET['id_order'] ?? '';

$data = mysqli_query($conn, "
    SELECT id_order, status 
    FROM orders 
    WHERE id_order='$id_order'
");

$trx = mysqli_fetch_assoc($data);

if (!$trx) {
    echo "Resi tidak ditemukan";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Lacak Pesanan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap');
        body {
            font-family: 'Inter', sans-serif;
        }
        .status-badge {
            transition: all 0.2s ease;
        }
        .progress-step {
            transition: all 0.3s ease;
        }
    </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-blue-50 flex items-center justify-center min-h-screen p-4">

<div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-white/20 backdrop-blur-sm">
    <!-- Header dengan aksen gradien -->
    <div class="bg-gradient-to-r from-blue-600 to-indigo-600 px-6 py-5">
        <div class="flex items-center gap-3">
            <div class="bg-white/20 rounded-xl p-2 backdrop-blur-sm">
                <i class="fas fa-search text-white text-lg"></i>
            </div>
            <div>
                <h2 class="text-white text-lg font-semibold tracking-wide">Lacak Pesanan</h2>
                <p class="text-blue-100 text-xs mt-0.5">Informasi status terkini</p>
            </div>
        </div>
    </div>
    
    <!-- Content -->
    <div class="p-6">
        <!-- Card Resi -->
        <div class="bg-slate-50 rounded-xl p-4 border border-slate-200/80 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500 font-medium uppercase tracking-wider">No. Resi</p>
                    <p class="text-lg font-bold text-slate-800 mt-1 tracking-wide"><?= $trx['id_order']; ?></p>
                </div>
                <div class="status-badge">
                    <?php
                    $status_colors = [
                        'pending' => 'bg-amber-100 text-amber-700 border-amber-200',
                        'diproses' => 'bg-blue-100 text-blue-700 border-blue-200',
                        'dikirim' => 'bg-purple-100 text-purple-700 border-purple-200',
                        'selesai' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'dibatalkan' => 'bg-red-100 text-red-700 border-red-200'
                    ];
                    $color = $status_colors[$trx['status']] ?? 'bg-gray-100 text-gray-700 border-gray-200';
                    ?>
                    <span class="<?= $color ?> px-4 py-2 rounded-full text-xs font-semibold border inline-flex items-center gap-1.5">
                        <i class="fas fa-circle text-[8px]"></i>
                        <?= strtoupper($trx['status']); ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Progress Tracking -->
        <div class="mb-4">
            <p class="text-xs font-semibold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
                <i class="fas fa-clock"></i>
                Tahapan Pesanan
            </p>
            
            <ul class="space-y-4">
                <!-- Diproses -->
                <li class="flex items-start gap-3 progress-step">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                            <?= in_array($trx['status'], ['diproses', 'dikirim', 'selesai']) 
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' 
                                : 'bg-slate-200 text-slate-400' ?>">
                            <i class="fas fa-<?= in_array($trx['status'], ['diproses', 'dikirim', 'selesai']) ? 'check' : 'circle' ?> text-xs"></i>
                        </div>
                        <?php if (in_array($trx['status'], ['dikirim', 'selesai'])): ?>
                        <div class="absolute top-8 left-4 w-0.5 h-8 bg-emerald-200"></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-medium <?= in_array($trx['status'], ['diproses', 'dikirim', 'selesai']) ? 'text-slate-800' : 'text-slate-400' ?>">
                            Pesanan diproses
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Verifikasi dan pengecekan</p>
                    </div>
                </li>

                <!-- Dikirim -->
                <li class="flex items-start gap-3 progress-step">
                    <div class="relative">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                            <?= in_array($trx['status'], ['dikirim', 'selesai']) 
                                ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' 
                                : 'bg-slate-200 text-slate-400' ?>">
                            <i class="fas fa-<?= in_array($trx['status'], ['dikirim', 'selesai']) ? 'check' : 'truck' ?> text-xs"></i>
                        </div>
                        <?php if ($trx['status'] == 'selesai'): ?>
                        <div class="absolute top-8 left-4 w-0.5 h-8 bg-emerald-200"></div>
                        <?php endif; ?>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-medium <?= in_array($trx['status'], ['dikirim', 'selesai']) ? 'text-slate-800' : 'text-slate-400' ?>">
                            Pesanan dikirim
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Dalam perjalanan ke alamat</p>
                    </div>
                </li>

                <!-- Selesai -->
                <li class="flex items-start gap-3 progress-step">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm
                        <?= $trx['status'] == 'selesai' 
                            ? 'bg-emerald-500 text-white shadow-lg shadow-emerald-200' 
                            : 'bg-slate-200 text-slate-400' ?>">
                        <i class="fas fa-<?= $trx['status'] == 'selesai' ? 'check' : 'box' ?> text-xs"></i>
                    </div>
                    <div class="flex-1 pt-1">
                        <p class="font-medium <?= $trx['status'] == 'selesai' ? 'text-slate-800' : 'text-slate-400' ?>">
                            Pesanan selesai
                        </p>
                        <p class="text-xs text-slate-400 mt-0.5">Pesanan telah diterima</p>
                    </div>
                </li>
            </ul>
        </div>

        <!-- Status Card untuk Dibatalkan -->
        <?php if ($trx['status'] == 'dibatalkan'): ?>
        <div class="mt-4 bg-gradient-to-r from-red-50 to-red-50/50 rounded-xl p-4 border border-red-200">
            <div class="flex items-center gap-3">
                <div class="bg-red-100 rounded-full p-2">
                    <i class="fas fa-times-circle text-red-500 text-lg"></i>
                </div>
                <div>
                    <p class="font-semibold text-red-700">Pesanan Dibatalkan</p>
                    <p class="text-xs text-red-600 mt-0.5">Transaksi tidak dapat dilanjutkan</p>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Tombol Kembali -->
        <div class="mt-8 pt-4 border-t border-slate-200">
            <a href="javascript:history.back()" 
               class="group flex items-center justify-center gap-2 w-full bg-slate-100 hover:bg-slate-200 text-slate-700 font-medium px-4 py-3 rounded-xl transition-all duration-200">
                <i class="fas fa-arrow-left text-sm group-hover:-translate-x-1 transition-transform"></i>
                <span>Kembali ke halaman sebelumnya</span>
            </a>
        </div>
    </div>
    
    <!-- Footer subtle -->
    <div class="bg-slate-50/50 px-6 py-3 border-t border-slate-200">
        <p class="text-xs text-center text-slate-400 flex items-center justify-center gap-2">
            <i class="fas fa-shield-alt text-slate-300"></i>
            Update status real-time
        </p>
    </div>
</div>

</body>
</html>