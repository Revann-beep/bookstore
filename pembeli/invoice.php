<?php
session_start();
require '../auth/connection.php';

$id_user  = $_SESSION['id_user'];
$id_order = $_GET['id_order'] ?? null;

if (!$id_order) {
    header("Location: status.php");
    exit;
}

/* HEADER */
$orderQ = mysqli_query($conn, "
    SELECT o.*, u.nama, u.email
    FROM orders o
    JOIN users u ON u.id_user = o.id_pembeli
    WHERE o.id_order = '$id_order'
      AND o.id_pembeli = '$id_user'
");

$order = mysqli_fetch_assoc($orderQ);
if (!$order) {
    die("Invoice tidak ditemukan");
}

/* DETAIL */
$detailQ = mysqli_query($conn, "
    SELECT *
    FROM order_details
    WHERE id_order = '$id_order'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice - Aksara Jiwa</title>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<style>
@media print {
    .no-print { display: none !important; }
    body { background: white !important; }
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
</style>
</head>

<body class="bg-gradient-to-b from-slate-50 to-slate-100 min-h-screen flex items-center justify-center relative p-4">

<!-- KEMBALI -->
<a href="status.php"
   class="no-print absolute top-6 left-6 flex items-center gap-2 bg-gradient-to-r from-slate-700 to-slate-800 text-white px-6 py-3 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5 z-10">
   <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
     <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
   </svg>
   Kembali ke Status
</a>

<!-- STRUK -->
<div class="bg-white w-full max-w-md print-invoice rounded-3xl shadow-2xl overflow-hidden border border-slate-100">

    <!-- HEADER -->
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
        <!-- INFO HEADER -->
        <div class="mb-8">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-slate-800">INVOICE PEMBAYARAN</h2>
                    <p class="text-slate-500 text-sm">Transaksi Buku Digital & Cetak</p>
                </div>
                <div class="text-right">
                    <div class="text-3xl font-bold text-amber-600">#<?= $order['id_order'] ?></div>
                    <div class="text-xs text-slate-500 mt-1">Order ID</div>
                </div>
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6">
                <div class="bg-gradient-to-br from-amber-50 to-amber-100 p-4 rounded-xl">
                    <p class="text-xs text-amber-800 font-medium mb-1">Tanggal Order</p>
                    <p class="font-semibold text-slate-800"><?= date('d F Y', strtotime($order['created_at'])) ?></p>
                    <p class="text-sm text-slate-600"><?= date('H:i', strtotime($order['created_at'])) ?> WIB</p>
                </div>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 p-4 rounded-xl">
                    <p class="text-xs text-slate-800 font-medium mb-1">Status Pembayaran</p>
                    <div class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-medium 
                        <?= $order['status'] == 'pending' ? 'bg-amber-100 text-amber-800' : 
                           ($order['status'] == 'paid' ? 'bg-green-100 text-green-800' : 
                           'bg-blue-100 text-blue-800') ?>">
                        <div class="w-2 h-2 rounded-full 
                            <?= $order['status'] == 'pending' ? 'bg-amber-500' : 
                               ($order['status'] == 'paid' ? 'bg-green-500' : 
                               'bg-blue-500') ?>"></div>
                        <?= ucfirst($order['status']) ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- INFO PEMBELI -->
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
                        <span class="text-white font-bold"><?= substr($order['nama'], 0, 1) ?></span>
                    </div>
                    <div>
                        <p class="font-semibold text-slate-800"><?= htmlspecialchars($order['nama']) ?></p>
                        <p class="text-sm text-slate-600"><?= htmlspecialchars($order['email']) ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- DETAIL ITEM -->
        <div class="mb-8">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-amber-600" viewBox="0 0 20 20" fill="currentColor">
                    <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z" />
                    <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
                </svg>
                Detail Pembelian
            </h3>
            
            <div class="space-y-4">
                <?php 
                mysqli_data_seek($detailQ, 0); // Reset pointer untuk loop
                while ($d = mysqli_fetch_assoc($detailQ)) : 
                ?>
                <div class="bg-gradient-to-br from-slate-50 to-slate-100 rounded-xl p-4">
                    <div class="flex justify-between items-start mb-2">
                        <div>
                            <p class="font-semibold text-slate-800"><?= htmlspecialchars($d['nama_buku']) ?></p>
                            <div class="flex items-center gap-4 mt-2">
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd" />
                                    </svg>
                                    Qty: <?= $d['qty'] ?>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-slate-600">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                        <path d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z" clip-rule="evenodd" />
                                    </svg>
                                    @Rp <?= number_format($d['harga'],0,',','.') ?>
                                </div>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-xl font-bold text-amber-600">Rp <?= number_format($d['subtotal_penjualan'],0,',','.') ?></p>
                            <p class="text-xs text-slate-500">Subtotal</p>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>
        </div>

        <!-- TOTAL -->
        <div class="bg-gradient-to-r from-slate-800 to-slate-900 rounded-2xl p-6 text-white mb-6">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <p class="text-sm text-slate-300">Total Pembayaran</p>
                    <p class="text-xs text-slate-400">Termasuk pajak yang berlaku</p>
                </div>
                <div class="text-right">
                    <p class="text-3xl font-bold">Rp <?= number_format($order['total_harga'],0,',','.') ?></p>
                </div>
            </div>
            
            <!-- REKENING DUMMY - DITAMBAHKAN DI ATAS TEKS TERIMA KASIH -->
            <?php if ($order['status'] == 'pending'): ?>
            <div class="border-t border-slate-700 pt-4 mb-4">
                <div class="mb-3">
                    <p class="text-xs text-slate-400 mb-2 text-center">Transfer ke rekening berikut:</p>
                    
                    <!-- REKENING 1 -->
                    <div class="flex items-center justify-between bg-slate-700/50 p-3 rounded-lg mb-2">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fas fa-university text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">BCA (Bank Central Asia)</p>
                                <p class="text-xs text-slate-300">A/N: PT Aksara Jiwa Indonesia</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-mono font-bold">123-456-7890</p>
                            <button type="button" 
                                    onclick="copyToClipboard('1234567890')"
                                    class="copy-btn text-xs text-blue-400 hover:text-blue-300 mt-1">
                                <i class="fas fa-copy mr-1"></i>Copy
                            </button>
                        </div>
                    </div>
                    
                    <!-- REKENING 2 -->
                    <div class="flex items-center justify-between bg-slate-700/50 p-3 rounded-lg">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-gradient-to-r from-green-500 to-green-600 flex items-center justify-center">
                                <i class="fas fa-university text-white text-sm"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold">Mandiri</p>
                                <p class="text-xs text-slate-300">A/N: PT Aksara Jiwa Indonesia</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-mono font-bold">987-654-3210</p>
                            <button type="button" 
                                    onclick="copyToClipboard('9876543210')"
                                    class="copy-btn text-xs text-green-400 hover:text-green-300 mt-1">
                                <i class="fas fa-copy mr-1"></i>Copy
                            </button>
                        </div>
                    </div>
                </div>
                
                <div class="bg-amber-900/30 border border-amber-800/50 rounded-lg p-3 mb-3">
                    <p class="text-xs text-amber-200 text-center">
                        <i class="fas fa-info-circle mr-1"></i>
                        Transfer tepat sesuai nominal di atas dan upload bukti pembayaran
                    </p>
                </div>
            </div>
            <?php endif; ?>
            
            <div class="border-t border-slate-700 pt-4">
                <p class="text-center text-sm text-slate-300">
                    Terima kasih telah berbelanja di Aksara Jiwa
                </p>
            </div>
        </div>

        <!-- FOOTNOTE -->
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

<!-- BUTTON ACTION -->
<div class="no-print absolute bottom-10 flex flex-col sm:flex-row gap-4">
    <button onclick="window.print()"
        class="flex items-center gap-2 bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white px-8 py-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
            <path fill-rule="evenodd" d="M5 4v3H4a2 2 0 00-2 2v3a2 2 0 002 2h1v2a2 2 0 002 2h6a2 2 0 002-2v-2h1a2 2 0 002-2V9a2 2 0 00-2-2h-1V4a2 2 0 00-2-2H7a2 2 0 00-2 2zm8 0H7v3h6V4zm0 8H7v4h6v-4z" clip-rule="evenodd" />
        </svg>
        Cetak Invoice
    </button>

    <?php if ($order['status'] == 'pending'): ?>
    <form action="../auth/upload-bukti.php" method="get" class="w-full sm:w-auto">
        <input type="hidden" name="id_order" value="<?= $id_order ?>">
        <button class="flex items-center gap-2 w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white px-8 py-4 rounded-full shadow-lg hover:shadow-xl transition-all duration-300 hover:-translate-y-0.5">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zM6.293 6.707a1 1 0 010-1.414l3-3a1 1 0 011.414 0l3 3a1 1 0 01-1.414 1.414L11 5.414V13a1 1 0 11-2 0V5.414L7.707 6.707a1 1 0 01-1.414 0z" clip-rule="evenodd" />
            </svg>
            Upload Bukti Bayar
        </button>
    </form>
    <?php endif; ?>
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
        showToast('Nomor rekening berhasil disalin!');
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

// Tambahkan style animasi untuk toast
const style = document.createElement('style');
style.textContent = `
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
`;
document.head.appendChild(style);
</script>

</body>
</html>