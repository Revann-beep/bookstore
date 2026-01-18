<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN SUPER ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../login.php");
    exit;
}

// Update last_activity Super Admin sendiri (opsional)
mysqli_query($conn, "UPDATE users SET last_activity=NOW() WHERE id_user='{$_SESSION['id_user']}'");

// HAPUS PENJUAL
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user='$id' AND role='penjual'");
    header("Location: penjual.php");
    exit;
}

// AMBIL DATA PENJUAL
$penjual = mysqli_query($conn, "SELECT * FROM users WHERE role='penjual' ORDER BY id_user DESC");

// Fungsi untuk cek status online/offline
function getStatus($last_activity) {
    if (!$last_activity) return 'offline';
    $last = strtotime($last_activity);
    $now  = time();
    $diff = $now - $last;
    return ($diff <= 300) ? 'online' : 'offline'; // 5 menit = online
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Penjual - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg flex flex-col">
        <div class="p-6 flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold">S</div>
            <span class="font-bold text-teal-600">SARI ANGREK</span>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">Dashboard</a>
            <a href="penjual.php" class="flex px-4 py-2 rounded-lg bg-teal-500 text-white">Data Penjual</a>
            <a href="pembeli.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">Data Pembeli</a>
            <a href="kategori.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">Kategori</a>
        </nav>

        <div class="px-4 pb-4 mt-auto">
            <a href="../auth/logout.php" class="block px-4 py-2 text-gray-500 hover:text-red-500">Sign Out</a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Data Akun Penjual</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($row = mysqli_fetch_assoc($penjual)) : 
                $statusOnline = getStatus($row['last_activity']);
                $foto = !empty($row['foto']) ? $row['foto'] : '../assets/default_user.png';
            ?>
            <div class="bg-white p-6 rounded-xl shadow flex flex-col items-center text-center relative">
                <!-- FOTO -->
                <img src="<?= $foto ?>" alt="Foto <?= htmlspecialchars($row['nama']) ?>" class="w-24 h-24 rounded-full object-cover mb-4">

                <!-- NAMA -->
                <h3 class="font-bold text-lg mb-1"><?= htmlspecialchars($row['nama']) ?></h3>

                <!-- EMAIL -->
                <p class="text-sm text-gray-500 mb-3"><?= htmlspecialchars($row['email']) ?></p>

                <!-- STATUS AKUN -->
                <?php if ($row['status'] === 'aktif'): ?>
                    <span class="bg-green-100 text-green-600 px-3 py-1 rounded-full text-xs mb-2 inline-block">Aktif</span>
                <?php else: ?>
                    <span class="bg-red-100 text-red-600 px-3 py-1 rounded-full text-xs mb-2 inline-block">Nonaktif</span>
                <?php endif; ?>

                <!-- STATUS ONLINE -->
                <div class="flex items-center gap-2 mb-4">
                    <span class="<?= $statusOnline === 'online' ? 'bg-green-500' : 'bg-red-500' ?> w-3 h-3 rounded-full inline-block"></span>
                    <span class="text-xs text-gray-600"><?= ucfirst($statusOnline) ?></span>
                </div>

                <!-- AKSI -->
                <div class="flex gap-2">
                    <a href="detail_penjual.php?id=<?= $row['id_user'] ?>" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">Detail</a>
                    <a href="?hapus=<?= $row['id_user'] ?>" onclick="return confirm('Yakin hapus penjual?')" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded">Hapus</a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </main>

</div>

</body>
</html>
