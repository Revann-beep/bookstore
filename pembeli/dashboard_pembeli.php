<?php
session_start();
require '../auth/connection.php';

/* CEK LOGIN */
if (!isset($_SESSION['id_user'])) {
    header("Location: ../auth/login.php");
    exit;
}



/* AMBIL DATA USER LOGIN */
$id_user = $_SESSION['id_user'];
$userQuery = mysqli_query($conn, "
    SELECT nama, email, image 
    FROM users 
    WHERE id_user = '$id_user'
");
$user = mysqli_fetch_assoc($userQuery);

/* AMBIL DATA KATEGORI */
$query = mysqli_query($conn, "SELECT * FROM kategori");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard - Aksara Jiwa</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&family=Playfair+Display:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body {
      font-family: 'Poppins', sans-serif;
    }
    .brand-font {
      font-family: 'Playfair Display', serif;
    }
  </style>
</head>
<body class="bg-gradient-to-br from-slate-50 to-slate-100 font-sans">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-gradient-to-b from-slate-900 to-slate-800 shadow-2xl p-6">
    <div class="mb-10">
      <h1 class="text-3xl font-bold text-amber-300 brand-font mb-1">AKSARA</h1>
      <h1 class="text-3xl font-bold text-amber-100 brand-font">JIWA</h1>
      <p class="text-slate-400 text-sm mt-2">Bookstore</p>
    </div>
    <nav class="space-y-2">
      <a href="dashboard_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-amber-500 to-amber-600 text-white font-medium shadow-lg hover:shadow-amber-200/30 transition-all duration-300">
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
      <a href="report.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11.707 4.707a1 1 0 00-1.414-1.414L10 9.586 8.707 8.293a1 1 0 00-1.414 0l-2 2a1 1 0 101.414 1.414L8 10.414l1.293 1.293a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
        </svg>
        Laporan
      </a>
      <!-- Help Section Added -->
      <a href="help.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-slate-700/50 hover:text-white transition-all duration-300">
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
      <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-slate-300 hover:bg-red-900/30 hover:text-red-200 transition-all duration-300 mt-8">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M3 3a1 1 0 00-1 1v12a1 1 0 102 0V4a1 1 0 00-1-1zm10.293 9.293a1 1 0 001.414 1.414l3-3a1 1 0 000-1.414l-3-3a1 1 0 10-1.414 1.414L14.586 9H7a1 1 0 100 2h7.586l-1.293 1.293z" clip-rule="evenodd" />
        </svg>
        Sign Out
      </a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 p-8">

    <!-- Topbar -->
    <div class="flex justify-end mb-8">
      <div class="flex items-center gap-3 bg-gradient-to-r from-slate-800 to-slate-900 px-5 py-3 rounded-xl shadow-lg hover:shadow-xl transition-all duration-300">
        
        <div class="text-right">
          <p class="font-semibold text-white">
            <?= htmlspecialchars($user['nama']); ?>
          </p>
          <p class="text-sm text-slate-300">
            <?= htmlspecialchars($user['email']); ?>
          </p>
        </div>

        <div class="relative">
          <img 
            src="../img/profile/<?= !empty($user['image']) ? $user['image'] : 'default.png'; ?>" 
            class="w-12 h-12 rounded-full object-cover border-2 border-amber-400"
          />
          <div class="absolute bottom-0 right-0 w-3 h-3 bg-green-500 rounded-full border-2 border-slate-900"></div>
        </div>
        
      </div>
    </div>

    <!-- Banner -->
    <div class="bg-gradient-to-r from-amber-600 to-amber-800 rounded-3xl p-10 text-white flex justify-between items-center mb-10 shadow-xl overflow-hidden relative">
      <div class="relative z-10">
        <h2 class="text-3xl font-bold mb-4 leading-tight brand-font">
          Temukan Dunia dalam Setiap Halaman<br>dan Nikmati Kenikmatan di Setiap Tegukan
        </h2>
        <p class="text-amber-100 mb-6 max-w-2xl">
          Aksara Jiwa adalah tempat di mana cerita bertemu dengan kopi, dan imajinasi bertemu dengan kenyamanan.
        </p>
        <div class="flex gap-4">
          <button class="bg-white text-amber-800 px-8 py-3 rounded-full font-semibold hover:bg-amber-50 hover:shadow-lg transition-all duration-300 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
              <path d="M9 4.804A7.968 7.968 0 005.5 4c-1.255 0-2.443.29-3.5.804v10A7.969 7.969 0 015.5 14c1.669 0 3.218.51 4.5 1.385A7.962 7.962 0 0114.5 14c1.255 0 2.443.29 3.5.804v-10A7.968 7.968 0 0014.5 4c-1.255 0-2.443.29-3.5.804V12a1 1 0 11-2 0V4.804z" />
            </svg>
            Jelajahi Buku
          </button>
          <button class="border border-amber-300 px-8 py-3 rounded-full hover:bg-amber-700/50 hover:border-amber-200 transition-all duration-300">Tentang Kami</button>
        </div>
      </div>
      <div class="relative">
        <img src="https://cdn-icons-png.flaticon.com/512/2232/2232688.png" class="w-48 relative z-10" />
        <div class="absolute -top-10 -right-10 w-64 h-64 bg-amber-400/20 rounded-full blur-3xl"></div>
      </div>
    </div>

    <!-- Kategori -->
    <div class="flex justify-between items-center mb-6">
      <div>
        <h3 class="text-2xl font-bold text-slate-800">Koleksi Buku Anda</h3>
        <p class="text-slate-600">Telusuri berdasarkan kategori favorit</p>
      </div>
      <a href="#" class="text-amber-600 font-medium hover:text-amber-800 flex items-center gap-1">
        Lihat Semua
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M12.293 5.293a1 1 0 011.414 0l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-2.293-2.293a1 1 0 010-1.414z" clip-rule="evenodd" />
        </svg>
      </a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
      <?php while ($row = mysqli_fetch_assoc($query)) : ?>
        <div class="bg-white rounded-2xl p-6 text-center shadow hover:shadow-xl transition-all duration-300 hover:-translate-y-1 group cursor-pointer border border-slate-100">
          <div class="w-16 h-16 mx-auto mb-4 flex items-center justify-center rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 group-hover:from-amber-100 group-hover:to-amber-200 transition-all duration-300">
            <img 
              src="../img/kategori/<?= $row['icon'] ? $row['icon'] : 'default.png'; ?>" 
              class="w-10 h-10"
              alt="<?= htmlspecialchars($row['nama_kategori']); ?>"
            >
          </div>
          <p class="font-semibold text-slate-800 group-hover:text-amber-700 transition-colors duration-300"><?= htmlspecialchars($row['nama_kategori']); ?></p>
        </div>
      <?php endwhile; ?>
    </div>

    <footer class="text-center text-slate-500 mt-16 pb-4">
      <div class="flex items-center justify-center gap-2 mb-2">
        <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
        <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
        <div class="w-8 h-1 bg-gradient-to-r from-amber-400 to-amber-600 rounded-full"></div>
      </div>
      <p>© <?= date('Y'); ?> <span class="text-amber-600 font-semibold">Aksara Jiwa</span> - Bookstore </p>
      <p class="text-sm mt-1"><?= htmlspecialchars($user['nama']); ?> | Member sejak 2023</p>
    </footer>

  </main>
</div>

</body>
</html>