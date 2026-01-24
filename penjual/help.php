<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES KALAU BELUM LOGIN
if (!isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit;
}

mysqli_query($conn, "
    UPDATE users 
    SET last_activity = NOW(),
        status = 'online'
    WHERE id_user = '$id_user'
");

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Bantuan | Aksara Jiwa</title>
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

    <!-- MENU - BISA SCROLL -->
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
        <a href="akun_saya.php" class="flex items-center gap-3 px-4 py-3 rounded-lg text-gray-700 hover:bg-indigo-50">
          <i class="fas fa-user-circle w-5"></i> Akun Saya
        </a>
        <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-indigo-50 text-indigo-600 font-medium">
          <i class="fas fa-question-circle w-5"></i> Bantuan
        </a>
      </nav>
    </div>

    <!-- LOGOUT - TETAP DI BAWAH -->
    <div class="p-4 border-t mt-auto">
      <a href="../auth/logout.php" class="flex items-center gap-3 text-red-500 hover:text-red-600">
        <i class="fas fa-sign-out-alt"></i> Keluar
      </a>
    </div>
  </aside>

  <!-- CONTENT - BISA SCROLL -->
  <main class="flex-1 ml-64 p-6 overflow-y-auto h-screen">
    <div class="mb-8">
      <h1 class="text-3xl font-bold text-gray-800 mb-2">Bantuan & Dukungan</h1>
      <p class="text-gray-600">Temukan solusi untuk masalah yang Anda hadapi</p>
    </div>

    <!-- TUTORIAL -->
    <section class="bg-white rounded-xl shadow p-6 mb-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-blue-600 rounded-lg flex items-center justify-center">
          <i class="fas fa-graduation-cap text-white text-xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Panduan Langkah Demi Langkah</h2>
      </div>

      <div class="space-y-4">
        <?php 
        $tutorials = [
          [
            'icon' => 'fas fa-plus-circle',
            'title' => 'Menambah Produk Baru',
            'desc' => 'Menu <strong>Produk</strong> → Klik tombol <strong>Tambah Produk</strong> → Isi data produk → Klik <strong>Simpan</strong>.',
            'color' => 'text-blue-600'
          ],
          [
            'icon' => 'fas fa-edit',
            'title' => 'Mengedit Produk',
            'desc' => 'Pada list produk, klik tombol <strong>Edit</strong> → Ubah data → Klik <strong>Simpan</strong>.',
            'color' => 'text-green-600'
          ],
          [
            'icon' => 'fas fa-trash-alt',
            'title' => 'Menghapus Produk',
            'desc' => 'Produk hanya bisa dihapus jika <strong>stok = 0</strong>. Jika stok masih > 0, tombol hapus akan dinonaktifkan.',
            'color' => 'text-red-600'
          ],
          [
            'icon' => 'fas fa-check-double',
            'title' => 'Menyetujui Transaksi',
            'desc' => 'Menu <strong>Approve</strong> → Pilih transaksi → Klik <strong>Approve</strong>.',
            'color' => 'text-purple-600'
          ],
          [
            'icon' => 'fas fa-chart-bar',
            'title' => 'Melihat Laporan',
            'desc' => 'Menu <strong>Laporan</strong> → Pilih periode → Klik <strong>Terapkan Filter</strong>.',
            'color' => 'text-indigo-600'
          ]
        ];
        
        foreach ($tutorials as $index => $tutorial): ?>
        <div class="flex items-start gap-4 p-4 rounded-lg hover:bg-gray-50 border border-gray-100">
          <div class="mt-1">
            <i class="<?= $tutorial['icon'] ?> text-lg <?= $tutorial['color'] ?>"></i>
          </div>
          <div>
            <h3 class="font-semibold text-gray-800 mb-1"><?= $index + 1 ?>. <?= $tutorial['title'] ?></h3>
            <p class="text-gray-600 text-sm"><?= $tutorial['desc'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- FAQ -->
    <section class="bg-white rounded-xl shadow p-6">
      <div class="flex items-center gap-3 mb-6">
        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-green-600 rounded-lg flex items-center justify-center">
          <i class="fas fa-question text-white text-xl"></i>
        </div>
        <h2 class="text-2xl font-bold text-gray-800">Pertanyaan yang Sering Diajukan</h2>
      </div>

      <div class="space-y-4">
        <?php 
        $faqs = [
          [
            'q' => 'Kenapa produk tidak bisa dihapus?',
            'a' => 'Karena stok masih > 0. Pastikan stok sudah 0 atau habiskan stok terlebih dahulu.'
          ],
          [
            'q' => 'Kenapa gambar produk tidak muncul?',
            'a' => 'Pastikan file gambar berhasil diupload dan tersimpan di folder <strong>img/produk</strong>. Format gambar harus JPG, PNG atau GIF.'
          ],
          [
            'q' => 'Kenapa saya tidak bisa login?',
            'a' => 'Pastikan email & password benar, dan status akun aktif. Jika lupa password, gunakan fitur reset password.'
          ],
          [
            'q' => 'Bagaimana cara mengubah foto profil?',
            'a' => 'Menu <strong>Akun Saya</strong> → Klik tombol <strong>Edit Profil</strong> → Pilih gambar → Upload → Simpan.'
          ],
          [
            'q' => 'Bagaimana cara mengunduh laporan?',
            'a' => 'Menu <strong>Laporan</strong> → Pilih periode → Klik tombol <strong>Download CSV</strong>.'
          ]
        ];
        
        foreach ($faqs as $index => $faq): ?>
        <div class="border border-gray-200 rounded-lg overflow-hidden">
          <button onclick="toggleFaq(<?= $index ?>)" 
                  class="w-full flex justify-between items-center p-4 text-left bg-gray-50 hover:bg-gray-100">
            <span class="font-medium text-gray-800"><?= $faq['q'] ?></span>
            <i class="fas fa-chevron-down text-gray-500" id="faqIcon<?= $index ?>"></i>
          </button>
          <div id="faqAnswer<?= $index ?>" class="hidden p-4 border-t">
            <p class="text-gray-600"><?= $faq['a'] ?></p>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </section>

    <!-- KONTAK -->
    <div class="mt-6 p-6 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-xl text-white">
      <div class="flex items-center gap-4 mb-4">
        <i class="fas fa-headset text-2xl"></i>
        <div>
          <h3 class="text-xl font-bold">Butuh Bantuan Lebih Lanjut?</h3>
          <p class="opacity-90">Tim support kami siap membantu Anda</p>
        </div>
      </div>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="flex items-center gap-3">
          <i class="fas fa-envelope"></i>
          <span>support@aksarajiwa.com</span>
        </div>
        <div class="flex items-center gap-3">
          <i class="fas fa-phone"></i>
          <span>+62 812 3456 7890</span>
        </div>
        <div class="flex items-center gap-3">
          <i class="fas fa-clock"></i>
          <span>Senin - Jumat, 08:00 - 17:00</span>
        </div>
      </div>
    </div>
  </main>
</div>

<script>
  function toggleFaq(index) {
    const answer = document.getElementById('faqAnswer' + index);
    const icon = document.getElementById('faqIcon' + index);
    
    answer.classList.toggle('hidden');
    icon.classList.toggle('fa-chevron-down');
    icon.classList.toggle('fa-chevron-up');
  }
</script>
</body>
</html>