<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN PENJUAL
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../login.php");
    exit;
}

$id_user = $_SESSION['id_user'];

// update last_activity
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='$id_user'");

// ambil data diri sendiri
$query = mysqli_query($conn, "SELECT * FROM users WHERE id_user='$id_user'");
$user = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Akun Saya</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans">

<div class="flex min-h-screen">

  <!-- SIDEBAR (SESUAI PUNYA KAMU) -->
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


  <!-- MAIN CONTENT -->
  <main class="flex-1 p-8">

    <h2 class="text-2xl font-bold text-gray-800 mb-6">Akun Saya</h2>

    <div class="max-w-sm">
      <div class="bg-white p-6 rounded-2xl shadow text-center">

        <img src="<?= $user['image'] ?: '../img/default_user.png' ?>"
             class="w-24 h-24 rounded-full object-cover mx-auto mb-4">

        <h3 class="font-bold text-lg"><?= htmlspecialchars($user['nama']) ?></h3>
        <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($user['email']) ?></p>

        <span class="inline-block bg-teal-100 text-teal-600 px-3 py-1 rounded-full text-xs mb-4">
          Akun Penjual
        </span>

        <a href="../auth/edit_akun.php"
           class="block bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
           Edit Akun
        </a>

      </div>
    </div>

  </main>

</div>
</body>
</html>
