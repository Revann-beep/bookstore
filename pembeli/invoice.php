<?php
session_start();
require '../auth/connection.php';

$id_user  = $_SESSION['id_user'];
$id_order = $_GET['id_order'] ?? null;

if (!$id_order) {
    header("Location: status.php");
    exit;
}

/* HEADER */
$orderQ = mysqli_query($conn, "
    SELECT o.*, u.nama, u.email
    FROM orders o
    JOIN users u ON u.id_user = o.id_pembeli
    WHERE o.id_order = '$id_order'
      AND o.id_pembeli = '$id_user'
");

$order = mysqli_fetch_assoc($orderQ);
if (!$order) {
    die("Invoice tidak ditemukan");
}

/* DETAIL */
$detailQ = mysqli_query($conn, "
    SELECT *
    FROM order_details
    WHERE id_order = '$id_order'
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Invoice</title>
<script src="https://cdn.tailwindcss.com"></script>

<style>
@media print {
    .no-print { display: none; }
    body { background: white; }
}
</style>
</head>

<body class="bg-slate-200 min-h-screen flex items-center justify-center relative">

<!-- KEMBALI -->
<a href="status.php"
   class="no-print absolute top-6 left-6 bg-gray-200 px-5 py-2 rounded-full hover:bg-gray-300">
   ← Kembali
</a>

<!-- STRUK -->
<div class="bg-white w-[320px] rounded-xl shadow-lg p-6 text-sm">

    <h2 class="text-center font-bold text-lg">SARI ANGGREK</h2>
    <p class="text-center text-gray-500 mb-4">Toko Buku</p>

    <hr class="my-3">

    <p><b>Pembeli:</b> <?= htmlspecialchars($order['nama']) ?></p>
    <p><b>Email:</b> <?= htmlspecialchars($order['email']) ?></p>
    <p><b>Order ID:</b> <?= $order['id_order'] ?></p>
    <p><b>Tanggal:</b> <?= date('d-m-Y H:i', strtotime($order['created_at'])) ?></p>

    <hr class="my-3">

    <!-- ITEM -->
    <?php while ($d = mysqli_fetch_assoc($detailQ)) : ?>
        <div class="flex justify-between mb-2">
            <div>
                <p class="font-semibold"><?= $d['nama_buku'] ?></p>
                <p class="text-gray-500">
                    <?= $d['qty'] ?> x Rp <?= number_format($d['harga'],0,',','.') ?>
                </p>
            </div>
            <p class="font-semibold">
                Rp <?= number_format($d['subtotal_penjualan'],0,',','.') ?>
            </p>
        </div>
    <?php endwhile; ?>

    <hr class="my-3">

    <div class="flex justify-between font-bold text-base">
        <span>Total</span>
        <span>Rp <?= number_format($order['total_harga'],0,',','.') ?></span>
    </div>

    <p class="text-center text-gray-500 mt-4">
        Terima kasih 🙏
    </p>
</div>

<!-- BUTTON -->
<div class="no-print absolute bottom-10 flex gap-6">
    <button onclick="window.print()"
        class="bg-emerald-500 text-white px-8 py-3 rounded-full shadow">
        🖨 Print
    </button>

    <form action="../auth/upload-bukti.php" method="get">
        <input type="hidden" name="id_order" value="<?= $id_order ?>">
        <button
            class="bg-blue-500 text-white px-8 py-3 rounded-full shadow">
            ⬆ Upload Bukti
        </button>
    </form>
</div>

</body>
</html>
