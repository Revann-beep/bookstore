<?php
session_start();
require '../auth/connection.php';

// CEK LOGIN
if (!isset($_SESSION['id_user'])) {
    header("Location: ../index.php");
    exit;
}

// HANYA PEMBELI YANG BOLEH CHAT
if ($_SESSION['role'] !== 'pembeli') {
    die("Akses ditolak");
}

$pengirim = $_SESSION['id_user']; // ID pembeli

// AMBIL PENJUAL BERDASARKAN ROLE ENUM
$qPenjual = mysqli_query($conn, "
    SELECT id_user 
    FROM users 
    WHERE role = 'penjual'
    LIMIT 1
");

$penjual = mysqli_fetch_assoc($qPenjual);

if (!$penjual) {
    die("Penjual tidak ditemukan");
}

$penerima = $penjual['id_user']; // ID penjual

// KIRIM PESAN
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pesan = mysqli_real_escape_string($conn, $_POST['pesan']);

    mysqli_query($conn, "
        INSERT INTO messages (sender_id, receiver_id, message)
        VALUES ('$pengirim', '$penerima', '$pesan')
    ");
}

// AMBIL CHAT
$chat = mysqli_query($conn, "
    SELECT * FROM messages
    WHERE 
        (sender_id='$pengirim' AND receiver_id='$penerima')
        OR
        (sender_id='$penerima' AND receiver_id='$pengirim')
    ORDER BY created_at ASC
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Chat Penjual</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex flex-col">

<!-- HEADER -->
<div class="bg-white shadow p-4 flex items-center">
    <a href="dashboard_pembeli.php"
       class="text-teal-600 font-semibold hover:underline">
        ← Kembali
    </a>
    <h2 class="mx-auto font-bold text-gray-800">
        Chat Penjual
    </h2>
</div>

<!-- CHAT AREA -->
<div class="flex-1 overflow-y-auto p-6 space-y-4">

<?php while ($row = mysqli_fetch_assoc($chat)) : ?>
    <?php if ($row['sender_id'] == $pengirim): ?>
        <!-- PESAN PEMBELI -->
        <div class="flex justify-end">
            <div class="bg-teal-500 text-white p-4 rounded-xl max-w-md">
                <?= htmlspecialchars($row['message']) ?>
            </div>
        </div>
    <?php else: ?>
        <!-- PESAN PENJUAL -->
        <div class="flex justify-start">
            <div class="bg-white p-4 rounded-xl shadow max-w-md">
                <?= htmlspecialchars($row['message']) ?>
            </div>
        </div>
    <?php endif; ?>
<?php endwhile; ?>

</div>

<!-- INPUT PESAN -->
<form method="POST" class="bg-white p-4 flex gap-3 border-t">
    <input type="text" name="pesan" placeholder="Ketik pesan..."
        class="flex-1 border rounded-xl px-4 py-3 focus:ring-2 focus:ring-teal-500"
        required>

    <button type="submit"
        class="bg-teal-500 hover:bg-teal-600 text-white px-6 py-3 rounded-xl font-semibold">
        Kirim
    </button>
</form>

</body>
</html>
