<?php
session_start();
require '../auth/connection.php';

$id_user  = $_SESSION['id_user'] ?? null;
$id_order = $_GET['id_order'] ?? null;

if (!$id_user || !$id_order) {
    header("Location: ../pembeli/status.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Upload Bukti Pembayaran</title>
<script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-slate-200 min-h-screen flex items-center justify-center">

<div class="bg-white w-[360px] p-6 rounded-xl shadow-lg">

<h2 class="text-center font-bold text-lg mb-4">
Upload Bukti Pembayaran
</h2>

<form action="../auth/proses-upload.php" method="POST" enctype="multipart/form-data">
    <input type="hidden" name="id_order" value="<?= $id_order ?>">

    <input type="file" name="bukti" accept="image/*" required
        class="w-full border p-3 rounded-lg mb-4">

    <button type="submit"
        class="w-full bg-emerald-500 text-white py-3 rounded-full">
        Upload
    </button>
</form>

<a href="../pembeli/invoice.php?id_order=<?= $id_order ?>"
   class="block text-center mt-4 text-gray-500 hover:underline">
   ← Kembali ke Invoice
</a>

</div>

</body>
</html>
