<?php
session_start();
require '../auth/connection.php';

/* ambil data order + item + produk */
$reportQ = mysqli_query($conn, "
    SELECT 
        o.id_order,
        o.total,
        o.metode_pembayaran,
        o.bukti,
        oi.qty,
        p.nama_buku
    FROM orders o
    JOIN order_items oi ON o.id_order = oi.id_order
    JOIN produk p ON oi.id_produk = p.id_produk
    ORDER BY o.created_at DESC
");
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sari Angrek - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        body {
            background-color: #f8fafc;
        }
    </style>
</head>
<body class="bg-gray-50">
    <div class="container mx-auto p-4 md:p-6">
        <!-- Header -->
        <header class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8">
            <div class="mb-4 md:mb-0">
                <h1 class="text-3xl font-bold text-gray-800">SARI ANGREK</h1>
                <p class="text-gray-600 mt-1">Dashboard</p>
            </div>
            
            <div class="flex items-center space-x-4">
                <div class="flex items-center space-x-2">
                    <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center">
                        <i class="fas fa-user text-indigo-600"></i>
                    </div>
                    <div class="hidden md:block">
                        <p class="font-medium">nurhayaturladilla</p>
                        <p class="text-sm text-gray-500">Admin</p>
                    </div>
                </div>
                <button class="bg-red-50 text-red-600 px-4 py-2 rounded-lg hover:bg-red-100 transition-colors">
                    <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                </button>
            </div>
        </header>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Content -->
            <div class="lg:col-span-2">
                <!-- Descender Card -->
                <div class="bg-gradient-to-r from-blue-500 to-indigo-600 rounded-2xl p-6 mb-8 text-white shadow-lg">
                    <div class="flex justify-between items-start">
                        <div>
                            <h2 class="text-xl font-medium mb-2">Descender</h2>
                            <p class="text-blue-100 mb-4">Pendapatan tahun ini</p>
                            <p class="text-5xl font-bold">2025</p>
                        </div>
                        <div class="bg-white/20 p-3 rounded-xl">
                            <i class="fas fa-chart-line text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Pesanan Table -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-gray-800">Kode Pesanan</h3>
                        <button class="text-indigo-600 hover:text-indigo-800 font-medium">
                            <i class="fas fa-plus mr-2"></i>Tambah Pesanan
                        </button>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Merek</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">QTY</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Bukti</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Metode Pembayaran</th>
                                    <th class="text-left py-3 px-4 text-gray-600 font-medium">Total Harga</th>
                                </tr>
                            </thead>
                            <tbody>
<?php if (mysqli_num_rows($reportQ) == 0): ?>
<tr>
  <td colspan="5" class="py-6 text-gray-500">Belum ada data pesanan</td>
</tr>
<?php endif; ?>

<?php while ($r = mysqli_fetch_assoc($reportQ)) : ?>
<tr class="border-b hover:bg-gray-50">
  <td class="py-4 px-4">
    <div class="flex items-center">
      <div class="bg-blue-100 text-blue-800 font-bold w-10 h-10 flex items-center justify-center rounded-lg mr-3">
        <?= $r['id_order'] ?>
      </div>
      <span class="font-medium"><?= htmlspecialchars($r['nama_buku']) ?></span>
    </div>
  </td>

  <td class="py-4 px-4">
    <span class="bg-gray-100 px-3 py-1 rounded-full"><?= $r['qty'] ?></span>
  </td>

  <td class="py-4 px-4">
    <?php if ($r['bukti']) : ?>
      <a href="../img/bukti/<?= $r['bukti'] ?>" target="_blank">
        <i class="fas fa-paperclip text-blue-500"></i>
      </a>
    <?php else : ?>
      <span class="text-gray-400">-</span>
    <?php endif; ?>
  </td>

  <td class="py-4 px-4">
    <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full">
      <?= htmlspecialchars($r['metode_pembayaran']) ?>
    </span>
  </td>

  <td class="py-4 px-4 font-bold">
    Rp <?= number_format($r['total'], 0, ',', '.') ?>
  </td>
</tr>
<?php endwhile; ?>
</tbody>

                        </table>
                    </div>
                    
                    <div class="mt-6 text-center text-gray-500 text-sm">
                        <p>Menampilkan 2 dari 2 pesanan</p>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- My Account Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">My Account</h3>
                    <div class="space-y-4">
                        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-50 text-gray-700 hover:text-indigo-600">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                <i class="fas fa-user-circle"></i>
                            </div>
                            <span>Profil Saya</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-50 text-gray-700 hover:text-indigo-600">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                <i class="fas fa-cog"></i>
                            </div>
                            <span>Pengaturan</span>
                        </a>
                        <a href="#" class="flex items-center p-3 rounded-lg hover:bg-gray-50 text-gray-700 hover:text-indigo-600">
                            <div class="w-10 h-10 rounded-full bg-gray-100 flex items-center justify-center mr-3">
                                <i class="fas fa-bell"></i>
                            </div>
                            <span>Notifikasi</span>
                            <span class="ml-auto bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center">3</span>
                        </a>
                        <button class="w-full flex items-center justify-center p-3 rounded-lg bg-red-50 text-red-600 hover:bg-red-100 mt-4">
                            <i class="fas fa-sign-out-alt mr-2"></i>Sign Out
                        </button>
                    </div>
                </div>

                <!-- Help Card -->
                <div class="bg-white rounded-2xl shadow-lg p-6">
                    <h3 class="text-xl font-bold text-gray-800 mb-4">Help</h3>
                    <div class="flex items-start mb-6">
                        <div class="w-12 h-12 rounded-full bg-indigo-100 flex items-center justify-center mr-4">
                            <i class="fas fa-question-circle text-indigo-600 text-xl"></i>
                        </div>
                        <div>
                            <p class="font-medium">Pusat Bantuan</p>
                            <p class="text-gray-600 text-sm">Temukan jawaban untuk pertanyaan umum</p>
                        </div>
                    </div>
                    
                    <div class="border-t pt-4">
                        <div class="flex items-center">
                            <div class="w-10 h-10 rounded-full bg-blue-100 flex items-center justify-center mr-3">
                                <i class="fas fa-at text-blue-600"></i>
                            </div>
                            <div>
                                <p class="font-medium">@nurhayaturladilla</p>
                                <p class="text-gray-600 text-sm">Hubungi admin</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-6">
                        <a href="#" class="block text-center bg-indigo-50 text-indigo-600 hover:bg-indigo-100 py-3 rounded-lg font-medium">
                            <i class="fas fa-headset mr-2"></i>Hubungi Dukungan
                        </a>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="bg-gradient-to-r from-cyan-500 to-blue-500 rounded-2xl shadow-lg p-6 text-white">
                    <h3 class="text-xl font-bold mb-4">Statistik Cepat</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="bg-white/20 p-4 rounded-xl">
                            <p class="text-sm opacity-90">Total Pesanan</p>
                            <p class="text-2xl font-bold">24</p>
                        </div>
                        <div class="bg-white/20 p-4 rounded-xl">
                            <p class="text-sm opacity-90">Pendapatan</p>
                            <p class="text-2xl font-bold">Rp 360K</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="mt-10 pt-6 border-t text-center text-gray-500 text-sm">
            <p>© 2023 Sari Angrek. Semua hak dilindungi undang-undang.</p>
            <p class="mt-1">Dashboard v2.1</p>
        </footer>
    </div>

    <script>
        // Menambahkan sedikit interaktivitas
        document.addEventListener('DOMContentLoaded', function() {
            // Tambahkan efek hover pada baris tabel
            const tableRows = document.querySelectorAll('tbody tr');
            tableRows.forEach(row => {
                row.addEventListener('mouseenter', function() {
                    this.style.transition = 'background-color 0.2s ease';
                });
            });
            
            // Alert untuk tombol sign out
            const signOutButtons = document.querySelectorAll('button.bg-red-50');
            signOutButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirm('Apakah Anda yakin ingin keluar?')) {
                        alert('Anda telah berhasil keluar.');
                    }
                });
            });
            
            // Alert untuk tombol tambah pesanan
            const addOrderButton = document.querySelector('button.text-indigo-600');
            if (addOrderButton) {
                addOrderButton.addEventListener('click', function() {
                    alert('Fitur tambah pesanan akan segera tersedia.');
                });
            }
        });
    </script>
</body>
</html>