<?php
session_start();
require '../auth/connection.php';

// Cegah cache browser
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

/* ===================== CEK LOGIN & ROLE ===================== */
if (!isset($_SESSION['id_user']) || $_SESSION['role'] !== 'penjual') {
    header("Location: ../index.php");
    exit;
}

/* ===================== CEK ID PENJUAL ===================== */
$id_penjual = $_GET['id'] ?? null;
if (!$id_penjual) {
    header("Location: admin.php");
    exit;
}

/* ===================== AMBIL DATA PENJUAL ===================== */
$query = mysqli_query($conn, "
    SELECT nama, email, alamat, image, status, created_at, last_activity
    FROM users
    WHERE id_user='$id_penjual' AND role='penjual'
");

if (mysqli_num_rows($query) === 0) {
    header("Location: admin.php");
    exit;
}

$data = mysqli_fetch_assoc($query);

/* ===================== STATUS ONLINE ===================== */
function statusOnline($last) {
    if (!$last) return 'offline';
    return (time() - strtotime($last) <= 300) ? 'online' : 'offline';
}

$online = statusOnline($data['last_activity']);
$image = !empty($data['image']) ? $data['image'] : '../assets/default_user.png';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Detail Penjual</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="bg-gray-100 min-h-screen p-6">

<div class="max-w-3xl mx-auto bg-white rounded-2xl shadow p-8">

    <!-- HEADER -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-xl font-bold text-gray-800">
            <i class="fas fa-store mr-2 text-indigo-600"></i> Profil Penjual
        </h1>
        <a href="admin.php" class="text-sm text-indigo-600 hover:underline">
            ← Kembali
        </a>
    </div>

    <!-- PROFILE -->
    <div class="flex flex-col md:flex-row gap-6">
        <!-- FOTO -->
        <div class="text-center">
            <img src="<?= htmlspecialchars($image) ?>"
                 class="w-28 h-28 rounded-full object-cover border shadow mx-auto">
            <p class="mt-2 text-sm font-medium <?= $online === 'online' ? 'text-green-600' : 'text-gray-500' ?>">
                <i class="fas fa-circle text-xs"></i> <?= ucfirst($online) ?>
            </p>
        </div>

        <!-- INFO -->
        <div class="flex-1 space-y-4">
            <div>
                <p class="text-sm text-gray-500">Nama</p>
                <p class="font-semibold"><?= htmlspecialchars($data['nama']) ?></p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Email</p>
                <p><?= htmlspecialchars($data['email']) ?></p>
            </div>

            <div>
                <p class="text-sm text-gray-500">Alamat</p>
                <p><?= htmlspecialchars($data['alamat'] ?? '-') ?></p>
            </div>

            <div class="flex gap-6">
                <div>
                    <p class="text-sm text-gray-500">Status Akun</p>
                    <span class="px-3 py-1 rounded-full text-sm font-medium
                        <?= $data['status'] === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-600' ?>">
                        <?= ucfirst($data['status']) ?>
                    </span>
                </div>

                <div>
                    <p class="text-sm text-gray-500">Bergabung</p>
                    <p><?= date('d M Y', strtotime($data['created_at'])) ?></p>
                </div>
            </div>
        </div>
    </div>

</div>

</body>
</html>