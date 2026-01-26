<?php
session_start();
require '../auth/connection.php';

// Validasi session dan parameter
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}

$id_user = $_SESSION['id_user'];
$id_order = $_GET['id_order'] ?? null;

if (!$id_order) {
    header("Location: status.php");
    exit;
}

// Validasi input untuk mencegah SQL injection
$id_order = mysqli_real_escape_string($conn, $id_order);
$id_user = mysqli_real_escape_string($conn, $id_user);

// Ambil data order
$orderQ = mysqli_query($conn, "
    SELECT o.*, u.nama, u.email, u.alamat
    FROM orders o
    JOIN users u ON u.id_user = o.id_pembeli
    WHERE o.id_order = '$id_order'
      AND o.id_pembeli = '$id_user'
");

$order = mysqli_fetch_assoc($orderQ);
if (!$order) {
    die("Invoice tidak ditemukan");
}

// Ambil detail order + data penjual dari users
$detailQ = mysqli_query($conn, "
    SELECT od.*, p.id_penjual, u.nama AS nama_penjual
    FROM order_details od
    JOIN produk p ON p.id_produk = od.id_produk
    JOIN users u ON u.id_user = p.id_penjual
    WHERE od.id_order = '$id_order'
");

// Ambil daftar penjual (unik)
$penjualList = [];
mysqli_data_seek($detailQ, 0);
while ($d = mysqli_fetch_assoc($detailQ)) {
    $penjualList[$d['id_penjual']] = $d['nama_penjual'];
}

// Ambil data bukti transfer
$bukti_tf = $order['bukti_tf'] ?? '';
$bukti_arr = $bukti_tf ? json_decode($bukti_tf, true) : [];

// Format tanggal
$tanggal_order = date('d F Y', strtotime($order['created_at']));
$waktu_order = date('H:i', strtotime($order['created_at']));

// Format status
$status = $order['status'];
$status_text = str_replace('_', ' ', ucfirst($status));

// Warna status
$status_colors = [
    'pending' => ['bg' => 'bg-amber-100', 'text' => 'text-amber-800', 'dot' => 'bg-amber-500'],
    'menunggu_verifikasi' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'dot' => 'bg-yellow-500'],
    'di_terima' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'dot' => 'bg-green-500'],
    'default' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'dot' => 'bg-blue-500']
];

$status_color = $status_colors[$status] ?? $status_colors['default'];

// Helper function untuk escape output
function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #<?= e($order['id_order']) ?> - Aksara Jiwa</title>
    
    <!-- CSS Libraries -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        @media print {
            .no-print { 
                display: none !important; 
            }
            body { 
                background: white !important; 
            }
            .print-invoice { 
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
                margin: 0 auto !important;
            }
        }
        
        body {
            font-family: 'Poppins', sans-serif;
        }
        
        .brand-font {
            font-family: 'Playfair Display', serif;
        }
        
        .copy-btn {
            transition: all 0.2s ease;
        }
        
        .copy-btn:hover {
            transform: scale(1.05);
        }
        
        .copy-btn:active {
            transform: scale(0.95);
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        @keyframes fadeOut {
            from { opacity: 1; transform: translateY(0); }
            to { opacity: 0; transform: translateY(-10px); }
        }
        
        .animate-fade-in {
            animation: fadeIn 0.3s ease forwards;
        }
        
        .animate-fade-out {
            animation: fadeOut 0.3s ease forwards;
        }
    </style>
</head>

<body class="bg-gradient-to-b from-slate-50 to-slate-100 min-h-screen flex items-center justify-center relative p-4">
    <!-- Tombol Kembali -->
    <a href="status.php"
       class="no-print absolute top-6 left-6 flex items-center gap-2 bg-gradient-to-r from-slate-700 to-slate-800 text-white px-6 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 z-10">
       <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
         <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
       </svg>
       Kembali ke Status
    </a>

    <!-- Struktur Invoice -->
    <div class="bg-white w-full max-w-md print-invoice rounded-3xl shadow-2xl overflow-hidden border border-slate-100">
        <!-- Header -->
        <div class="bg-gradient-to-r from-amber-600 to-amber-800 p-8 text-white text-center">
            <div class="mb-4">
                <h1 class="text-3xl font-bold brand-font mb-1">AKSARA JIWA</h1>
                <p class="text-amber-200 text-sm">Bookstore & Digital Publishing</p>
            </div>
            <div class="flex items-center justify-center gap-2">
                <div class="w-3 h-3 bg-green-400 rounded-full animate-pulse"></div>
                <p class="text-sm">Invoice Digital • Pembayaran Aman</p>
            </div>
        </div>

        <div class="p-8">
            <!-- Informasi Header -->
            <div class="mb-8">
                <div class="flex justify-between items-start mb-6">
                    <div>
                        <h2 class="text-2xl font-bold text-slate-800">INVOICE PEMBAYARAN</h2>
                        <p class="text-slate-500 text-sm">Transaksi Buku Digital & Cetak</p>
                    </div>
                    <div class="text-right">
                        <div class="text-3xl font-bold text-amber-600">#<?= e($order['id_order']) ?></div>
                        <div class="text-xs text-slate-500 mt-1">Order ID</div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-xl">
                        <p class="text-xs text-amber-800 font-medium mb-1">Tanggal Order</p>
                        <p class="font-semibold text-slate-800"><?= e($tanggal_order) ?></p>
                        <p class="text-sm text-slate-600"><?= e($waktu_order) ?> WIB</p>
                    </div>
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-4 rounded-xl">
                        <p class="text-xs text-slate-800 font-medium mb-1">Status Pembayaran</p>
                        <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium <?= $status_color['bg'] . ' ' . $status_color['text'] ?>">
                            <div class="w-2 h-2 rounded-full <?= $status_color['dot'] ?>"></div>
                            <?= e($status_text) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Informasi Pembeli -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    Informasi Pembeli
                </h3>
                <div class="space-y-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                            <span class="text-white font-bold"><?= substr(e($order['nama']), 0, 1) ?></span>
                        </div>
                        <div>
                            <p class="font-semibold text-slate-800"><?= e($order['nama']) ?></p>
                            <p class="text-sm text-slate-600"><?= e($order['email']) ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Detail Item -->
            <div class="mb-8">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                        <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                    </svg>
                    Detail Pembelian
                </h3>
                
                <div class="space-y-4">
                    <?php mysqli_data_seek($detailQ, 0); ?>
                    <?php while ($d = mysqli_fetch_assoc($detailQ)) : ?>
                    <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <p class="font-semibold text-slate-800"><?= e($d['nama_buku']) ?></p>
                                <div class="flex items-center gap-4 mt-2">
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                        </svg>
                                        Qty: <?= e($d['qty']) ?>
                                    </div>
                                    <div class="flex items-center gap-2 text-sm text-slate-600">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                            <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                        </svg>
                                        @Rp <?= number_format($d['harga'], 0, ',', '.') ?>
                                    </div>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="text-xl font-bold text-amber-600">Rp <?= number_format($d['subtotal_penjualan'], 0, ',', '.') ?></p>
                                <p class="text-xs text-slate-500">Subtotal</p>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
            </div>

            <!-- Total Pembayaran -->
            <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-6 text-white mb-6">
                <div class="flex justify-between items-center mb-4">
                    <div>
                        <p class="text-sm text-slate-300">Total Pembayaran</p>
                        <p class="text-xs text-slate-400">Termasuk pajak yang berlaku</p>
                    </div>
                    <div class="text-right">
                        <p class="text-3xl font-bold">Rp <?= number_format($order['total_harga'], 0, ',', '.') ?></p>
                    </div>
                </div>

                <!-- Form Upload Bukti untuk Status Pending -->
                
                <!-- Alamat Pengiriman -->
                <div class="border-t border-slate-700 pt-4 mb-4">
                    <p class="text-xs text-slate-400 mb-3 text-center">Informasi Pengiriman</p>
                    
                    <!-- Alamat Penjual -->
                    <div class="mb-3">
                        <p class="text-xs text-slate-400 mb-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-amber-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd" />
                            </svg>
                            Dikirim Dari:
                        </p>
                        <div class="bg-slate-700/50 p-3 rounded-lg">
                            <p class="text-sm font-semibold text-white mb-1">Aksara Jiwa Bookstore</p>
                            <p class="text-xs text-slate-300">Jl. Literasi No. 123, Gedung Buku Lantai 3</p>
                            <p class="text-xs text-slate-300">Jakarta Pusat, DKI Jakarta 10110</p>
                            <p class="text-xs text-slate-300 mt-1">📞 (021) 1234-5678</p>
                        </div>
                    </div>
                    
                    <!-- Alamat Pembeli -->
                    <div class="mb-3">
                        <p class="text-xs text-slate-400 mb-2 flex items-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                            </svg>
                            Dikirim Ke:
                        </p>
                        <div class="bg-slate-700/50 p-3 rounded-lg border border-slate-600">
                            <p class="text-sm font-semibold text-white mb-1"><?= e($order['nama']) ?></p>
                            <?php if (!empty($order['alamat'])): ?>
                                <p class="text-xs text-slate-300"><?= e($order['alamat']) ?></p>
                            <?php else: ?>
                                <p class="text-xs text-amber-300 italic">Alamat belum diisi. Silakan lengkapi profil Anda.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <!-- Informasi Pengiriman -->
                    <div class="mt-3 bg-blue-900/20 border border-blue-800/30 rounded-lg p-3">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                                <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4v1a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H20a1 1 0 001-1v-4a1 1 0 00-.293-.707l-2-2A1 1 0 0017 9h-1V4a1 1 0 00-1-1H3zm11 3v4h2.586l1 1H16a1 1 0 00-1-1h-1V7a1 1 0 00-1-1H8a1 1 0 00-1 1v4a1 1 0 001 1h1a1 1 0 001-1V9a1 1 0 011-1h4z" />
                            </svg>
                            <div>
                                <p class="text-xs text-blue-300 font-medium mb-1">Estimasi Pengiriman</p>
                                <p class="text-xs text-blue-200">
                                    📦 Akan diproses setelah pembayaran diverifikasi (3-5 hari kerja)
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <?php if ($status == 'pending'): ?>
                    <div class="bg-amber-900/30 border border-amber-800/50 rounded-lg p-3 mb-3">
                        <p class="text-xs text-amber-200 text-center">
                            <i class="fas fa-info-circle mr-1"></i>
                            Transfer tepat sesuai nominal di atas dan upload bukti pembayaran
                        </p>
                    </div>
                    <?php endif; ?>
                </div>

                <!-- Informasi Status Lainnya -->
                <?php if ($status != 'pending'): ?>
                <!-- Nomor Resi -->
                <?php if (!empty($order['no_resi'])): ?>
                <div class="bg-green-900/20 border border-green-800/30 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-400 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 00-2 2v4a2 2 0 002 2h12a2 2 0 002-2V6a2 2 0 00-2-2H4zm12 2h-4v4h4V6zm-5 0H4v4h7V6z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-xs text-green-300 font-medium mb-1">Nomor Resi Pengiriman</p>
                                <p class="text-sm font-mono font-bold text-green-200"><?= e($order['no_resi']) ?></p>
                            </div>
                        </div>
                        <button type="button" 
                                onclick="copyToClipboard('<?= e($order['no_resi']) ?>', this)"
                                class="copy-btn text-xs bg-green-800 hover:bg-green-700 text-white px-3 py-1 rounded-lg">
                            <i class="fas fa-copy mr-1"></i>Copy
                        </button>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Link Lacak -->
                <?php if (!empty($order['link_lacak'])): ?>
                <div class="bg-blue-900/20 border border-blue-800/30 rounded-lg p-3 mb-3">
                    <div class="flex items-center justify-between">
                        <div class="flex items-start gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M12.586 4.586a2 2 0 112.828 2.828l-3 3a2 2 0 01-2.828 0 1 1 0 00-1.414 1.414 4 4 0 005.656 0l3-3a4 4 0 00-5.656-5.656l-1.5 1.5a1 1 0 101.414 1.414l1.5-1.5zm-5 5a2 2 0 012.828 0 1 1 0 101.414-1.414 4 4 0 00-5.656 0l-3 3a4 4 0 105.656 5.656l1.5-1.5a1 1 0 10-1.414-1.414l-1.5 1.5a2 2 0 11-2.828-2.828l3-3z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <p class="text-xs text-blue-300 font-medium mb-1">Link Lacak Pengiriman</p>
                                <p class="text-xs text-blue-200 truncate max-w-[200px]">
                                    <?= e($order['link_lacak']) ?>
                                </p>
                            </div>
                        </div>
                        <a href="<?= e($order['link_lacak']) ?>" 
                           target="_blank" 
                           class="flex items-center gap-2 text-xs bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-lg">
                            <i class="fas fa-external-link-alt"></i>
                            Lacak
                        </a>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Status Pengiriman -->
                <div class="bg-blue-900/20 border border-blue-800/30 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-blue-400 mt-0.5 flex-shrink-0" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M8 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM15 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
                            <path d="M3 4a1 1 0 00-1 1v10a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H10a1 1 0 001-1v-1h4v1a1 1 0 001 1h1.05a2.5 2.5 0 014.9 0H20a1 1 0 001-1v-4a1 1 0 00-.293-.707l-2-2A1 1 0 0017 9h-1V4a1 1 0 00-1-1H3zm11 3v4h2.586l1 1H16a1 1 0 00-1-1h-1V7a1 1 0 00-1-1H8a1 1 0 00-1 1v4a1 1 0 001 1h1a1 1 0 001-1V9a1 1 0 011-1h4z" />
                        </svg>
                        <div>
                            <p class="text-xs text-blue-300 font-medium mb-1">Status Pengiriman</p>
                            <p class="text-xs text-blue-200">
                                <?php 
                                switch($status) {
                                    case 'pending':
                                        echo "⏳ Menunggu pembayaran";
                                        break;
                                    case 'menunggu_verifikasi':
                                        echo "🔍 Menunggu verifikasi pembayaran";
                                        break;
                                    case 'di_terima':
                                        if (!empty($order['no_resi'])) {
                                            echo "🚚 Pesanan sedang dikirim";
                                        } else {
                                            echo "📦 Pesanan diterima";
                                        }
                                        break;
                                    default:
                                        echo "📦 Pesanan sedang diproses";
                                }
                                ?>
                            </p>
                            <?php if ($status == 'di_terima' && !empty($order['no_resi'])): ?>
                            <p class="text-xs text-blue-300 mt-1">⏰ Estimasi: 3-5 hari kerja</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                
                <!-- Bukti Transfer yang Sudah Diupload -->
                <?php if (!empty($bukti_arr)): ?>
                <div class="mt-6 bg-white rounded-2xl p-6 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Bukti Transfer yang Sudah Diupload</h3>

                    <?php foreach ($bukti_arr as $id_penjual => $bukti): ?>
                    <div class="mb-4 p-4 rounded-xl border border-slate-200">
                        <p class="font-semibold text-slate-800 mb-2">
                            Penjual: <?= e($penjualList[$id_penjual] ?? $id_penjual) ?>
                        </p>
                        <img src="../img/bukti/<?= e($bukti['file']) ?>" width="200" alt="bukti transfer" class="rounded-lg">
                        <p class="mt-2 text-sm text-slate-600">Status: <?= e($bukti['status']) ?></p>
                        <p class="text-xs text-slate-400">Upload: <?= e($bukti['uploaded_at']) ?></p>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <?php if ($status == 'pending'): ?>
                <div class="mt-6 bg-white rounded-2xl p-6 border border-slate-200">
                    <h3 class="text-lg font-bold text-slate-800 mb-4">Upload Bukti Transfer (Per Penjual)</h3>

                    <?php foreach ($penjualList as $id_penjual => $nama_penjual): ?>
                    <div class="mb-4 p-4 rounded-xl border border-slate-200">
                        <p class="font-semibold text-slate-800 mb-2">Untuk Penjual: <?= e($nama_penjual) ?></p>

                        <form action="../auth/proses-upload.php" method="post" enctype="multipart/form-data">
                            <input type="hidden" name="id_order" value="<?= e($id_order) ?>">
                            <input type="hidden" name="id_penjual" value="<?= e($id_penjual) ?>">

                            <input type="file" name="bukti" accept="image/*" required class="w-full mb-3">
                            <button type="submit"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-xl">
                                Upload Bukti untuk <?= e($nama_penjual) ?>
                            </button>
                        </form>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                
                <!-- Pesan Terima Kasih -->
                <div class="border-t border-slate-700 pt-4">
                    <p class="text-center text-sm text-slate-300">
                        Terima kasih telah berbelanja di Aksara Jiwa
                    </p>
                </div>
            </div>

            <!-- Footnote -->
            <div class="mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center justify-center gap-2 mb-4">
                    <div class="w-6 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                    <div class="w-6 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                    <div class="w-6 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
                </div>
                <p class="text-center text-xs text-slate-500">
                    Invoice ini sah dan dapat digunakan sebagai bukti pembayaran.<br>
                    © <?= date('Y'); ?> Aksara Jiwa - Bookstore & Digital Publishing
                </p>
            </div>
        </div>
    </div>

    <!-- Tombol Action -->
    <div class="no-print absolute bottom-10 flex flex-col sm:flex-row gap-4">
        <button onclick="window.print()"
            class="flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-8 py-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
            </svg>
            Cetak Invoice
        </button>

    </div>

    <script>
    // Fungsi copy to clipboard
    function copyToClipboard(text, button = null) {
        navigator.clipboard.writeText(text.replace(/-/g, '')).then(() => {
            // Tampilkan feedback
            const originalText = button ? button.innerHTML : '';
            if (button) {
                button.innerHTML = '<i class="fas fa-check mr-1"></i>Copied!';
                button.classList.add('text-green-400');
                button.classList.remove('text-blue-400', 'text-green-400', 'hover:text-blue-300', 'hover:text-green-300');
                
                setTimeout(() => {
                    button.innerHTML = originalText;
                    button.classList.remove('text-green-400');
                    if (text.includes('123')) {
                        button.classList.add('text-blue-400', 'hover:text-blue-300');
                    } else {
                        button.classList.add('text-green-400', 'hover:text-green-300');
                    }
                }, 2000);
            }
            
            // Tampilkan toast notification
            showToast('Berhasil disalin!');
        }).catch(err => {
            console.error('Gagal menyalin: ', err);
            showToast('Gagal menyalin, coba lagi', 'error');
        });
    }

    // Fungsi toast notification
    function showToast(message, type = 'success') {
        // Hapus toast sebelumnya jika ada
        const existingToast = document.getElementById('copy-toast');
        if (existingToast) existingToast.remove();
        
        // Buat toast baru
        const toast = document.createElement('div');
        toast.id = 'copy-toast';
        toast.className = `fixed top-6 right-6 px-4 py-3 rounded-lg shadow-xl z-50 animate-fade-in ${type === 'success' ? 'bg-emerald-500 text-white' : 'bg-red-500 text-white'}`;
        toast.innerHTML = `
            <div class="flex items-center gap-2">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'}"></i>
                <span>${message}</span>
            </div>
        `;
        
        document.body.appendChild(toast);
        
        // Hapus toast setelah 3 detik
        setTimeout(() => {
            if (toast.parentNode) {
                toast.classList.add('animate-fade-out');
                setTimeout(() => {
                    if (toast.parentNode) toast.remove();
                }, 300);
            }
        }, 3000);
    }

    // Add print styling
    document.addEventListener('DOMContentLoaded', function() {
        const printBtn = document.querySelector('button[onclick="window.print()"]');
        if (printBtn) {
            printBtn.addEventListener('click', function() {
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            });
        }
    });
    </script>
</body>
</html>