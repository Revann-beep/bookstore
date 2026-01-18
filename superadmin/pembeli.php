<?php
session_start();
require '../auth/connection.php';

// CEGAH AKSES SELAIN SUPER ADMIN
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'super_admin') {
    header("Location: ../index.php");
    exit;
}

// HAPUS PEMBELI
if (isset($_GET['hapus'])) {
    $id = $_GET['hapus'];
    mysqli_query($conn, "DELETE FROM users WHERE id_user='$id' AND role='pembeli'");
    header("Location: pembeli.php");
    exit;
}

// AMBIL DATA PEMBELI
$pembeli = mysqli_query($conn, "
    SELECT * FROM users 
    WHERE role='pembeli'
    ORDER BY id_user DESC
");

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
    <title>Data Pembeli - Super Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

<div class="flex min-h-screen">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow-lg flex flex-col">
        <div class="p-6 flex items-center gap-2">
            <div class="w-10 h-10 rounded-full bg-teal-500 text-white flex items-center justify-center font-bold">
                S
            </div>
            <span class="font-bold text-teal-600">SARI ANGREK</span>
        </div>

        <nav class="flex-1 px-4 space-y-2">
            <a href="dashboard.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">
                Dashboard
            </a>
            <a href="penjual.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">
                Data Penjual
            </a>
            <a href="pembeli.php" class="flex px-4 py-2 rounded-lg bg-teal-500 text-white">
                Data Pembeli
            </a>
            <a href="kategori.php" class="flex px-4 py-2 rounded-lg hover:bg-teal-100 text-gray-600">
                Kategori
            </a>
        </nav>

        <div class="px-4 pb-4">
            <a href="../auth/logout.php" class="block px-4 py-2 text-gray-500 hover:text-red-500">
                Sign Out
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <main class="flex-1 p-8">

        <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-800">Data Akun Pembeli</h2>
        </div>

        <!-- CARDS -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <?php while ($row = mysqli_fetch_assoc($pembeli)) : 
                $statusOnline = getStatus($row['last_activity']);
            ?>
            <div class="bg-white rounded-xl shadow p-4 flex flex-col items-center">
                <!-- image -->
                <?php if ($row['image'] && file_exists('uploads/'.$row['image'])): ?>
                    <img src="uploads/<?= $row['image'] ?>" class="w-20 h-20 rounded-full object-cover mb-3">
                <?php else: ?>
                    <div class="w-20 h-20 rounded-full bg-gray-200 flex items-center justify-center mb-3">
                        <span class="text-gray-500 font-bold text-lg"><?= strtoupper($row['nama'][0]) ?></span>
                    </div>
                <?php endif; ?>

                <!-- Nama -->
                <h3 class="font-bold text-gray-800"><?= htmlspecialchars($row['nama']) ?></h3>

                <!-- Email -->
                <p class="text-sm text-gray-500 mb-2"><?= htmlspecialchars($row['email']) ?></p>

                <!-- Status -->
                <span class="px-3 py-1 rounded-full text-xs <?= $row['status'] === 'aktif' ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600' ?>">
                    <?= ucfirst($row['status']) ?>
                </span>

                <!-- Online / Offline -->
                <div class="mt-2">
                    <?php if ($statusOnline === 'online'): ?>
                        <span class="bg-green-500 w-3 h-3 rounded-full inline-block mr-1"></span>
                        <span class="text-xs text-gray-600">Online</span>
                    <?php else: ?>
                        <span class="bg-red-500 w-3 h-3 rounded-full inline-block mr-1"></span>
                        <span class="text-xs text-gray-600">Offline</span>
                    <?php endif; ?>
                </div>

                <!-- Aksi -->
                <div class="mt-3 flex gap-2">
                    <a href="detail_pembeli.php?id=<?= $row['id_user'] ?>" 
                       class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-sm">
                       Detail
                    </a>
                    <a href="?hapus=<?= $row['id_user'] ?>" 
                       onclick="return confirm('Yakin hapus pembeli?')"
                       class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                       Hapus
                    </a>
                </div>
            </div>
            <?php endwhile; ?>
        </div>

    </main>

</div>

</body>
</html>
