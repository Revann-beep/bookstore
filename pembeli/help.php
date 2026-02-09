<?php
session_start();
require '../auth/connection.php';

// CEK ROLE
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'pembeli') {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Help - Pembeli</title>
    <script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 font-sans min-h-screen">

    <!-- Main Container with Flex -->
    <div class="flex min-h-screen">
        
        <!-- Sidebar -->
        <aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl p-6">
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
          <path fill-rule="evenodd" d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v11a2 2 0 01-2 2H6a2 2 0 01-2-2V5xm3 4a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z" clip-rule="evenodd" />
        </svg>
        Produk
      </a>
                <a href="halaman-keranjang.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path d="M3 1a1 1 0 000 2h1.22l.305 1.222a.997.997 0 00.01.042l1.358 5.43-.893.892C3.74 11.846 4.632 14 6.414 14H15a1 1 0 000-2H6.414l1-1H14a1 1 0 00.894-.553l3-6A1 1 0 0017 3H6.28l-.31-1.243A1 1 0 005 1H3zM16 16.5a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0zM6.5 18a1.5 1.5 0 100-3 1.5 1.5 0 000 3z" />
                    </svg>
                    Keranjang
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
                <!-- Help Menu - Active State -->
                <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-3a1 1 0 00-.867.5 1 1 0 11-1.731-1A3 3 0 0113 8a3.001 3.001 0 01-2 2.83V11a1 1 0 11-2 0v-1a1 1 0 011-1 1 1 0 100-2zm0 8a1 1 0 100-2 1 1 0 000 2z" clip-rule="evenodd" />
                    </svg>
                    Help
                </a>
                <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                    </svg>
                    My Account
                </a>
                <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-red-300 hover:bg-red-900/30 hover:text-red-200 transition-all duration-300 mt-8">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
                    </svg>
                    Sign Out
                </a>
            </nav>
        </aside>

        <!-- Main Content Area -->
        <div class="flex-1 flex flex-col">
            
            <!-- Header -->
            <div class="flex items-center p-4 bg-white shadow-md">
                <button onclick="window.history.back()" class="mr-4 px-3 py-2 bg-gray-200 rounded-lg hover:bg-gray-300 flex items-center gap-2">
                    <i class="fas fa-arrow-left"></i> Kembali
                </button>
                <h1 class="text-xl font-semibold">Panduan Pembeli</h1>
            </div>

            <!-- Konten Help -->
            <div class="flex-1 p-6">
                <div class="max-w-3xl mx-auto bg-white rounded-xl shadow-md p-6 space-y-6">
                    
                    <!-- Section 1 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">1. Cara Melihat Produk</h2>
                        <p>Untuk melihat produk yang tersedia, klik menu <span class="font-medium">Produk</span> di dashboard. Semua buku yang tersedia akan ditampilkan dalam bentuk grid. Anda bisa melihat nama, harga, dan stok buku di sini.</p>
                    </div>

                    <!-- Section 2 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">2. Menambahkan Produk ke Keranjang</h2>
                        <p>Jika Anda ingin membeli buku, klik tombol <span class="font-medium">Tambah ke Keranjang</span> pada produk yang diinginkan. Produk akan otomatis masuk ke keranjang Anda.</p>
                    </div>

                    <!-- Section 3 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">3. Melihat dan Mengelola Keranjang</h2>
                        <p>Untuk melihat produk yang sudah dipilih, klik menu <span class="font-medium">Keranjang</span>. Di sini, Anda bisa menambah, mengurangi, atau menghapus produk sebelum checkout.</p>
                    </div>

                    <!-- Section 4 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">4. Checkout</h2>
                        <p>Setelah memilih produk, klik tombol <span class="font-medium">Checkout</span>. Pastikan jumlah produk sudah sesuai. Jika Anda memiliki poin member, diskon akan otomatis diterapkan.</p>
                    </div>

                    <!-- Section 5 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">5. Melihat Riwayat Pembelian</h2>
                        <p>Untuk melihat semua transaksi yang telah dilakukan, klik menu <span class="font-medium">Riwayat</span>. Anda bisa melihat detail setiap transaksi, termasuk tanggal dan total belanja.</p>
                    </div>

                    <!-- Section 6 -->
                    <div>
                        <h2 class="text-lg font-semibold mb-2">6. Menghubungi Support</h2>
                        <p>Jika Anda memiliki pertanyaan lebih lanjut, silakan hubungi admin melalui tombol <span class="font-medium">Contact Support</span> di dashboard atau melalui WhatsApp yang tersedia.</p>
                    </div>

                </div>
            </div>

        </div>
    </div>

</body>
</html>