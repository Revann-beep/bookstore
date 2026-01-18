<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES KALAU BELUM LOGIN
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Help - Kasir</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-100">

  <!-- SIDEBAR -->
  <div class="flex">
    <aside class="w-64 bg-white shadow-lg flex flex-col h-screen">

  <!-- LOGO -->
  <div class="p-6 flex items-center gap-2 border-b">
    <div class="w-10 h-10 bg-teal-500 text-white rounded-full flex items-center justify-center font-bold">
      S
    </div>
    <span class="font-bold text-teal-600">SARI ANGREK</span>
  </div>

  <!-- MENU -->
  <div class="flex-1 px-4 py-6 space-y-2 text-sm">

    <!-- Dashboard -->
    <a href="dashboard.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      📊 Dashboard
    </a>

    <!-- Produk -->
    <a href="produk.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      📦 Produk
    </a>

    <!-- Approve -->
   <div class="border border-gray-200 rounded-lg">
      <button onclick="toggleApprove()"
              class="w-full flex items-center justify-between px-4 py-2 text-gray-700 hover:bg-teal-100">
        <span class="flex items-center gap-3">
          ✅ Approve
        </span>
        <span id="iconApprove">▼</span>
      </button>

      <div id="approveMenu" class="hidden px-4 pb-2 space-y-2">
        <a href="approve.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Approve
        </a>
        <a href="laporan.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Laporan
        </a>
        <a href="chat.php" class="block px-3 py-2 rounded-lg hover:bg-gray-100">
          Chat
        </a>
        
      </div>
    </div>

    <!-- My Account -->
    <a href="akun_saya.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-700">
      👤 My Account
    </a>

    <!-- Sign Out -->
    <a href="../auth/logout.php"
       class="flex items-center gap-3 px-4 py-2 rounded-lg hover:bg-teal-100 text-red-500">
      🔒 Sign Out
    </a>

  </div>

  <!-- HELP (paling bawah) -->
  <div class="px-4 py-4 border-t">
    <a href="help.php"
       class="flex items-center gap-3 text-gray-500 hover:text-teal-600">
      ❓ Help
    </a>
  </div>

</aside>


    <!-- CONTENT -->
    <main class="flex-1 p-8">
      <h1 class="text-3xl font-bold mb-6">Help Center</h1>

      <!-- TUTORIAL -->
      <section class="bg-white p-6 rounded-lg shadow-md mb-6">
        <h2 class="text-2xl font-semibold mb-4">📌 Tutorial Step-by-Step</h2>

        <div class="space-y-4 text-gray-700">
          <div>
            <h3 class="font-semibold">1. Cara Menambah Produk</h3>
            <p class="text-sm">
              Menu <strong>Produk</strong> → Klik tombol <strong>Tambah</strong> → Isi data produk → Klik <strong>Simpan</strong>.
            </p>
          </div>

          <div>
            <h3 class="font-semibold">2. Cara Mengedit Produk</h3>
            <p class="text-sm">
              Pada list produk, klik tombol <strong>Edit</strong> → Ubah data → Klik <strong>Simpan</strong>.
            </p>
          </div>

          <div>
            <h3 class="font-semibold">3. Cara Menghapus Produk</h3>
            <p class="text-sm">
              Produk hanya bisa dihapus jika <strong>stok = 0</strong>.  
              Jika stok masih > 0, tombol hapus akan dinonaktifkan.
            </p>
          </div>

          <div>
            <h3 class="font-semibold">4. Cara Approve Transaksi</h3>
            <p class="text-sm">
              Menu <strong>Approve</strong> → Pilih transaksi → Klik <strong>Approve</strong>.
            </p>
          </div>

          <div>
            <h3 class="font-semibold">5. Cara Melihat Laporan</h3>
            <p class="text-sm">
              Menu <strong>Laporan</strong> → Pilih jenis laporan (harian/bulanan/tahunan) → Klik <strong>Tampilkan</strong>.
            </p>
          </div>
        </div>
      </section>

      <!-- FAQ -->
      <section class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-2xl font-semibold mb-4">❓ FAQ</h2>

        <div class="space-y-4 text-gray-700">
          <div>
            <h3 class="font-semibold">Q: Kenapa produk tidak bisa dihapus?</h3>
            <p class="text-sm">A: Karena stok masih > 0. Pastikan stok sudah 0.</p>
          </div>

          <div>
            <h3 class="font-semibold">Q: Kenapa gambar produk tidak muncul?</h3>
            <p class="text-sm">A: Pastikan file gambar berhasil upload dan tersimpan di folder <strong>img/produk</strong>.</p>
          </div>

          <div>
            <h3 class="font-semibold">Q: Kenapa saya tidak bisa login?</h3>
            <p class="text-sm">A: Pastikan email & password benar, dan status akun aktif.</p>
          </div>

          <div>
            <h3 class="font-semibold">Q: Bagaimana cara mengubah foto profil?</h3>
            <p class="text-sm">A: Masuk ke menu <strong>My Account</strong> → pilih gambar → upload → simpan. Foto profil hanya bisa diganti 7 hari sekali.</p>
          </div>
        </div>
      </section>
    </main>
  </div>

  <script>
    function toggle(id) {
      const el = document.getElementById(id);
      el.classList.toggle('hidden');
    }
  </script>
</body>
</html>
