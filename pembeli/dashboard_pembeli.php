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
  <title>Dashboard - Sari Anggrek</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 font-sans">

<div class="flex min-h-screen">

  <!-- Sidebar -->
  <aside class="w-64 bg-white shadow-lg p-6">
    <h1 class="text-2xl font-bold text-teal-500 mb-10">SARI<br>ANGGREK</h1>
    <nav class="space-y-3">
      <a href="dashboard_pembeli.php" class="flex items-center gap-3 px-4 py-3 rounded-xl bg-gradient-to-r from-teal-400 to-teal-500 text-white">Dashboard</a>
      <a href="halaman-pesanan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Pesanan</a>
      <a href="status.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Status</a>
      <a href="pesan.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Chat</a>
      <a href="report.html" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">Laporan</a>
      <a href="my.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-teal-50">My Account</a>
      <a href="../auth/logout.php" class="flex items-center gap-3 px-4 py-3 rounded-xl text-gray-500 hover:bg-red-50">Sign Out</a>
    </nav>
  </aside>

  <!-- Main -->
  <main class="flex-1 p-8">

    <!-- Topbar -->
    <!-- Topbar -->
<div class="flex justify-end mb-8">
  <div class="flex items-center gap-3 bg-white px-5 py-3 rounded-xl shadow">
    
    <div class="text-right">
      <p class="font-semibold">
        <?= htmlspecialchars($user['nama']); ?>
      </p>
      <p class="text-sm text-gray-500">
        <?= htmlspecialchars($user['email']); ?>
      </p>
    </div>

    <img 
      src="../img/profile/<?= !empty($user['image']) ? $user['image'] : 'default.png'; ?>" 
      class="w-10 h-10 rounded-full object-cover"
    />
    
  </div>
</div>


    <!-- Banner -->
    <div class="bg-gradient-to-r from-teal-400 to-teal-500 rounded-3xl p-8 text-white flex justify-between items-center mb-10">
      <div>
        <h2 class="text-2xl font-semibold mb-4">
          Sari Anggrek selalu didepan<br>melayani kebutuhan anda
        </h2>
        <div class="flex gap-4">
          <button class="bg-white text-teal-500 px-6 py-2 rounded-full font-semibold">Get Started</button>
          <button class="border border-white px-6 py-2 rounded-full">Learn More</button>
        </div>
      </div>
      <img src="https://cdn-icons-png.flaticon.com/512/29/29302.png" class="w-36" />
    </div>

    <!-- Kategori -->
    <div class="flex justify-between items-center mb-6">
      <h3 class="text-xl font-semibold">Your Book</h3>
      <a href="#" class="text-teal-500 font-medium">See All</a>
    </div>

    <div class="grid grid-cols-2 md:grid-cols-5 gap-6">
      <?php while ($row = mysqli_fetch_assoc($query)) : ?>
        <div class="bg-white rounded-2xl p-6 text-center shadow hover:shadow-lg transition">
          <img 
            src="../img/kategori/<?= $row['icon'] ? $row['icon'] : 'default.png'; ?>" 
            class="w-14 mx-auto mb-3"
            alt="<?= htmlspecialchars($row['nama_kategori']); ?>"
          >
          <p class="font-semibold"><?= htmlspecialchars($row['nama_kategori']); ?></p>
        </div>
      <?php endwhile; ?>
    </div>

    <footer class="text-center text-gray-400 mt-12">
      © <?= date('Y'); ?> <?= htmlspecialchars($user['nama']); ?>
    </footer>

  </main>
</div>

</body>
</html>
